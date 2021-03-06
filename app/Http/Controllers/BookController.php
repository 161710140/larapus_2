<?php

namespace App\Http\Controllers;

use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\DataTables;
use App\Book;
use App\Http\Requests\UpdateBookRequest;
use Illuminate\Http\Request;
use Session;
use App\Http\Requests\StoreBookRequest;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\BorrowLog;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\BookException;
use Excel;

class BookController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function borrow($id)
    {
    try {
        $book = Book::findOrFail($id);
        Auth::user()->borrow($book);
        Session::flash("flash_notification", 
        ["level"=>"success","message"=>"Berhasil meminjam $book->title"
    ]);
    } catch (BookException $e) {
        Session::flash("flash_notification",
        ["level" => "danger","message" => $e->getMessage()
    ]);
    } catch (ModelNotFoundException $e) {
        Session::flash("flash_notification", ["level"=>"danger","message"=>"Buku tidak ditemukan."
    ]);
    }
        return redirect('/');
    }
    public function returnBack($book_id){
            $borrowLog = BorrowLog::where('user_id', Auth::user()->id)->where('book_id', $book_id)->where('is_returned', 0)->first();
            if ($borrowLog) {
            $borrowLog->is_returned = true;
            $borrowLog->save();
            Session::flash("flash_notification", [
            "level"=> "success","message" => "Berhasil mengembalikan " . $borrowLog->book->title
        ]);
            }
            return redirect('/home');
    }


    public function index(Request $request, Builder $htmlBuilder)
    {
        if ($request->ajax()) {
        $books = Book::with('author');
        return Datatables::of($books)->addColumn('action', function($book){
        return view('datatable._action', [
        'model'=> $book,
        'form_url'=> route('books.destroy', $book->id),
        'edit_url'=> route('books.edit', $book->id),
        'confirm_message' => 'Yakin mau menghapus ' . $book->title . '?'
        ]);
        })->make(true);
        }
        $html = $htmlBuilder->addColumn(['data' => 'title', 'name'=>'title', 'title'=>'Judul'])
        ->addColumn(['data' => 'amount', 'name'=>'amount', 'title'=>'Jumlah'])
        ->addColumn(['data' => 'author.name', 'name'=>'author.name', 'title'=>'Penulis'])
        ->addColumn(['data' => 'action', 'name'=>'action', 'title'=>'', 'orderable'=>false, 'searchable'=>false]);
        return view('books.index')->with(compact('html'));
    
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('books.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
        public function store(StoreBookRequest $request) 
        { 
            $book = Book::create($request->except('cover'));
            if ($request->hasFile('cover')) { 
            $uploaded_cover = $request->file('cover');
            $extension = $uploaded_cover->getClientOriginalExtension();
            $filename = md5(time()) . '.' . $extension;
            $destinationPath = public_path() . DIRECTORY_SEPARATOR . 'img'; $uploaded_cover->move($destinationPath, $filename);
            $book->cover = $filename; $book->save();
            }
            Session::flash("flash_notification", 
            [ "level"=>"success", "message"=>"Berhasil menyimpan $book->title" ]);
            return redirect()->route('books.index');
        }

    /**
     * Display the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function show(Book $book)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function edit($id) { 
        $book = Book::find($id);
         return view('books.edit')->with(compact('book')); }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Book  $book
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBookRequest $request, $id) { 
        $book = Book::find($id); 
        if(!$book->update($request->all())) return redirect()->back();
        if ($request->hasFile('cover')) 
        { 
        $filename = null; $uploaded_cover = $request->file('cover'); 
        $extension = $uploaded_cover->getClientOriginalExtension();
        $filename = md5(time()) . '.' . $extension; $destinationPath = public_path() . DIRECTORY_SEPARATOR . 'img';
        $uploaded_cover->move($destinationPath, $filename);
        if ($book->cover) { $old_cover = $book->cover; $filepath = public_path() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $book->cover;
        try { File::delete($filepath); } catch (FileNotFoundException $e) {
        }
        }
        $book->cover = $filename; 
        $book->save();
        }
        Session::flash("flash_notification",
        [ "level"=>"success", "message"=>"Berhasil menyimpan $book->title" 
        ]);
        return redirect()->route('books.index');
    } 
        
        
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Book  $book$cover
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) 
    { 
        $book = Book::find($id);
        $cover = $book->cover;
        if(!$book->delete()) return redirect()->back();
        if ($cover) {
        $old_cover = $book->cover; $filepath = public_path() . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . $book->cover;
        try { 
            File::delete($filepath); 
        }
         catch (FileNotFoundException $e) {  
        }

        }
        Session::flash("flash_notification", [ "level"=>"success", "message"=>"Buku berhasil dihapus" ]);
        return redirect()->route('books.index');
        }

        public function export() 
        { 
            return view('books.export');
        }

        public function exportPost(Request $request) 
        { 
            // validasi
$this->validate($request, [
    'author_id'=>'required',
    ], [
    'author_id.required'=>'Anda belum memilih penulis. Pilih minimal 1 penulis.'
    ]);
    $books = Book::whereIn('id', $request->get('author_id'))->get();
    Excel::create('Data Buku Larapus', function($excel) use ($books) {
    // Set property
    $excel->setTitle('Data Buku Larapus')
    ->setCreator(Auth::user()->name);
    $excel->sheet('Data Buku', function($sheet) use ($books) {
    $row = 1;
    $sheet->row($row, [
    'Judul',
    'Jumlah',
'Stok',
'Penulis'
]);
foreach ($books as $book) {
$sheet->row(++$row, [
$book->title,
$book->amount,
$book->stock,
$book->author->name
]);
}
});
})->export('xls');
        }
    }
