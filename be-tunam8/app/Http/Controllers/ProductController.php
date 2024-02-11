<?php

namespace App\Http\Controllers;

use App\Logics\ProductImages as ProductImagesLogic;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function allProducts()
    {
        $products = Product::with(['category', 'images'])->paginate(10);

        // Get base URL
        $baseUrl = config('app.url');

        // Modify each product's images link with base URL
        $products->map(function ($product) use ($baseUrl) {
            $product->images->map(function ($image) use ($baseUrl) {
                $image->link = $baseUrl . '/products/' . $image->link;
                return $image;
            });
            return $product;
        });

        return response()->json($products);
    }

    public function createProduct(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'required',
            'images' => 'required|array|min:1|max:4',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $product = new Product;
        $product->name = $request->name;
        $product->category_id = $request->category_id;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->description = $request->description;
        $product->slug = Str::slug(round(microtime(true) * 1000) . $request->name, '-');
        $product->save();

        $productLogic = new ProductImagesLogic();
        if ($product->save()) {
            $imageCount = $productLogic->countImages($request->product_id);
            $addImages = $productLogic->addProductImage($imageCount, $product->id, $request->file('images'));
        } else {
            return response()->json(
                [
                    'message' => 'Err: Cannot add product',
                ],
                500
            );
        }

        // Get base URL
        $baseUrl = config('app.url') . '/products';

        // Modify product images link with base URL
        if ($addImages) {
            $product->images = $product->with('images')->find($product->id)->images->map(function ($image) use ($baseUrl) {
                $image->link = $baseUrl . '/' . $image->link;
                return $image;
            });

            return response()->json(
                [
                    'message' => 'Product created',
                    'product' => [
                        $product
                    ],
                ],
                201
            );
        } else {
            return response()->json(
                [
                    'message' => 'Err: Cannot add images to product',
                ],
                500
            );
        }
    }

    public function updateProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'description' => 'required',
        ]);

        $productClass = new Product();

        $product = $productClass->find($request->id);

        if ($product->name !== $request->name) {
            $validated['slug'] = Str::slug(round(microtime(true) * 1000) . $validated['name'], '-');
        }

        if ($product !== null) {
            $product->update(
                $validated
            );

            $productWithImages = $productClass->with(['category', 'images'])->find($request->id);

            $baseURL = config('app.url') . '/products';
            $productWithImages->images->map(function ($image) use ($baseURL) {
                $image->link = $baseURL . '/' . $image->link;
                return $image;
            });
            return response()->json(
                [
                    'message' => 'Product updated',
                    'product' => $productWithImages,
                ],
                200
            );
        }
        return response()->json(
            [
                'message' => 'Product not found',
            ],
            404
        );
    }

    public function deleteProduct(Request $request)
    {
        $product = Product::find($request->id);
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function getProduct($slug)
    {
        $product = Product::with(['category', 'images'])->where('slug', $slug)->first();

        if ($product == null) {
            return response()->json([
                'message' => 'Product not found',
            ], 404);
        }

        // Adding base URL to image links
        $baseURL = config('app.url') . '/products'; // Replace 'https://example.com' with your actual base URL
        $product->images->map(function ($image) use ($baseURL) {
            $image->link = $baseURL . '/' . $image->link;
            return $image;
        });

        return response()->json($product);
    }
}
