<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk
     */
    public function index() : View
    {
        $products = Product::paginate(10); // Tampilkan 10 produk per halaman
        return view('dashboard', compact('products'));

    }
    
    public function dashboard() : View
    {
        $products = Product::paginate(10); // Pastikan data dikirim ke view
        return view('index', compact('products'));
    }
    

    /**
     * Menampilkan halaman tambah produk
     */
    public function create(): View
    {
        return view('products.create');
    }

    /**
     * Menyimpan produk baru
     */
    public function store(Request $request): RedirectResponse
{
    //validate form
    $request->validate([
        'image'         => 'required|image|mimes:jpeg,jpg,png|max:2048',
        'title'         => 'required|min:5',
        'description'   => 'required|min:10',
        'price'         => 'required|numeric',
        'stock'         => 'required|numeric'
    ]);

    // Upload image
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imagePath = $image->store('products', 'public'); 
    } else {
        return back()->withErrors(['image' => 'File gambar tidak ditemukan']);
    }

    //create product
    Product::create([
        'image'         => $imagePath, // Simpan path gambar
        'title'         => $request->title,
        'description'   => $request->description,
        'price'         => $request->price,
        'stock'         => $request->stock
    ]);

    //redirect to index
    return redirect()->route('dashboard')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function show(string $id): View
    {
        // get product by ID
        $product = Product::findOrFail($id);

        // render view with product
        return view('products.show', compact('product'));
    }

    public function edit(string $id): View
    {
        // get product by ID
        $product = Product::findOrFail($id);

        //render view with product
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        // Validate form
        $request->validate([
            'image'         => 'image|mimes:jpeg,jpg,png|max:2048',
            'title'         => 'required|min:5',
            'description'   => 'required|min:10',
            'price'         => 'required|numeric',
            'stock'         => 'required|numeric'
        ]);
    
        // Get product by ID
        $product = Product::findOrFail($id);
    
        // Check if image is uploaded
        if ($request->hasFile('image')) {
    
            // Upload new image
            $image = $request->file('image');
            $imagePath = $image->store('products', 'public'); 
    
            // Delete old image
            Storage::delete($product->image);
    
            // Update product with new image
            $product->update([
                'image'         => $imagePath, // Simpan path yang sesuai
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);
    
        } else {
    
            // Update product without changing image
            $product->update([
                'title'         => $request->title,
                'description'   => $request->description,
                'price'         => $request->price,
                'stock'         => $request->stock
            ]);
        }
    
        // Redirect to index
        return redirect()->route('dashboard')->with(['success' => 'Data Berhasil Diubah!']);
    }
    


        public function destroy($id): RedirectResponse
        {
            //get id product by id
            $product = Product::findOrFail($id);

            //delete image
            Storage::delete('public/products/'. $product->image);

            //delete product
            $product->delete();

            //redirect to index
            return redirect()->route('dashboard')->with(['success' => 'Data Berhasil Dihapus!']);
        }       
    }