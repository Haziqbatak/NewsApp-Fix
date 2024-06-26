<?php

namespace App\Http\Controllers\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //title halaman index
        // title itu untuk memberikan judul halaman
        $title = 'Category - Index';
        //mengurutkan data berdasarkan data terbaru dengan paginate
        // paginate itu untuk membatasi data yang ditampilkan
        // jika data yang ditampilkan lebih dari 5 maka akan muncul halaman
        // jika kurang dari 5 maka tidak muncul halaman
        $category = Category::latest()->paginate(5);

        // compact itu untuk mengirim data ke view
        return view('home.category.index', compact(
            'category',
            'title'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //title halaman create
        // title itu untuk memberikan judul halaman
        $title = 'Category - Create';

        // compact itu untuk mengirim data ke view
        return view('home.category.create', compact(
            'title'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //melakukan validasi data
        $messages = [
            'name.required' => 'Nama kategori wajib diisi',
            'name.max' => 'Nama kategori maksimal 255 karakter',
            'image.image' => 'File yang diupload harus gambar',
            'image.mimes' => 'File yang diupload harus berformat jpeg, png, jpg',
            'image.max' => 'Ukuran gambar maksimal 5MB',
            'image.required' => 'Gambar wajib diisi'
        ];

        //membuat validasi
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'image' => 'image|mimes:jpeg,png,jpg|max:5120|required'
        ], $messages);

        //jika validasi gagal
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        //upload image
        $image = $request->file('image');

        //mengambil nama file
        $image->storeAs('public/category/', $image->hashName());

        //create data
        Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'image' => $image->hashName()
        ]);

        //redirect jika sukses menyimpan data
        return redirect()->route('category.index')->with('success', 'Category Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //title halaman edit
        $title = 'Category - Edit';

        //get data category by id
        //fungsi get data by id adalah mengambil data
        //berdasarkan id yang diinputkan
        $category = Category::find($id);

        return view('home.category.edit', compact(
            'category',
            'title'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //melakukan validasi data
        $this->validate($request, [
            'name' => 'required|max:100',
            'image' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

        //get data category by id
        $category = Category::find($id);

        //jika image kosong (tidak diupdate)
        if ($request->file('image') == '') {
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name)
            ]);
            return redirect()->route('category.index');
        } else {
            //jika gambarnya diupdate
            // hapus image lama
            Storage::disk('local')
                ->delete('public/category/'
                    . basename($category->image));

            //upload image baru
            $image = $request->file('image');
            $image->storeAs('public/category/', $image->hashName());

            //update data
            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'image' => $image->hashName()
            ]);

            return redirect()->route('category.index')->with('success', 'Category Updated Successfully');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //get data by id
        $category = Category::find($id);

        //delete image
        //basename itu untuk mengambil nama file
        Storage::disk('local')
            ->delete('public/category/' . basename($category->image));

        //delete data by id
        $category->delete();

        return redirect()
            ->route('category.index')->with('success', 'Category Deleted Successfully');
    }
}
