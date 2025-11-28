<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Http\Resources\ProductResource;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     * GET /api/products?page=1&per_page=10&cat_id=1&search=keyword
     */
    public function index(Request $request): JsonResponse
    {
       
        $perPage = $request->input('per_page', 10);
        
        $query = Product::select('id', 'name','description', 'sku','basic_price', 'product_category_id','id_store', 'is_active')
            ->with([
                'category:id,name', 
                'attributes:id,id_product,type,name,image,price_adjustment,qty' 
            ]);
       
        if($request->has('id_store')){
            if(isset($request->id_store)){
                $query->where();
            }
        }
        // Filter by category
        if ($request->has('cat_id')){
            if(isset($request->cart_id)){
                $query->where('product_category_id', $request->cat_id);
            }else{
                $query->whereNotNull('product_category_id');
            }   
        }
        
        // Search by product name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        
        $products = $query->paginate($perPage);
       
        return $this->sendResponse([
            'data' => ProductResource::collection($products->items()),
            'pagination' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ]
        ], 'Products retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/products
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id_seller' => 'required|exists:users,id',
            'product_category_id' => 'required|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            
          ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {

            $product = Product::create([
                'id_seller' => $request->id_seller,
                'product_category_id' => $request->product_category_id,
                'name' => $request->name,
                'sku' => $request->sku,
                'basic_price' =>$request->basic_price,
                'description' => $request->description,
                'is_active' => $request->is_active ?? true,
            ]);

            $attributes = $request->input('attributes', []);
            // Simpan attributes jika ada
            if ($request->has('attributes') && is_array($attributes)) {  
                foreach ($attributes as $index => $attributeData) {
                    
                    // Handle image upload jika ada
                    if ($attributeData['image']) {
                        $image = $attributeData['image'];
                    }else{
                        $image = "";
                    }
                    
                    $product->attributes()->create([
                        'id_product' => $attributeData['id_product'],
                        'type' => $attributeData['type'],
                        'name' => $attributeData['name'],
                        'image' => $image,
                        'price_adjustment' => $attributeData['price_adjustment'] ?? 0,
                        'qty' => $attributeData['qty'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            
            $product->load(['category', 'attributes']);
            return $this->sendResponse(new ProductResource($product), 'Product created successfully.', 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to create product.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     * GET /api/products/{id}
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::with(['category', 'attributes'])->find($id);

        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     * PUT/PATCH /api/products/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'id_seller' => 'sometimes|required|exists:users,id',
            'product_category_id' => 'sometimes|required|exists:product_categories,id',
            'name' => 'sometimes|required|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $id . '|max:100',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            
            // Validation untuk attributes
            'attributes' => 'nullable|array',
            'attributes.*.id' => 'nullable|exists:product_attributes,id',
            'attributes.*.type' => 'required_with:attributes|string|max:50',
            'attributes.*.name' => 'required_with:attributes|string|max:255',
            'attributes.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attributes.*.price_adjustment' => 'nullable|numeric',
            'attributes.*.qty' => 'nullable|numeric|min:0',
            'attributes.*._destroy' => 'nullable|boolean', // Flag untuk delete attribute
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            // Update product data
            $product->update($request->only([
                'id_seller',
                'product_category_id',
                'name',
                'sku',
                'description',
                'is_active'
            ]));

            // Update attributes jika ada
            if ($request->has('attributes') && is_array($request->attributes)) {
                $existingAttributeIds = [];
                
                foreach ($request->attributes as $index => $attributeData) {
                    // Check jika ini request untuk delete attribute
                    if (isset($attributeData['_destroy']) && $attributeData['_destroy']) {
                        if (isset($attributeData['id'])) {
                            $attr = ProductAttribute::find($attributeData['id']);
                            if ($attr && $attr->id_product == $product->id) {
                                // Delete image jika ada
                                if ($attr->image && Storage::disk('public')->exists($attr->image)) {
                                    Storage::disk('public')->delete($attr->image);
                                }
                                $attr->delete();
                            }
                        }
                        continue;
                    }
                    
                    $imagePath = null;
                    
                    // Handle image upload jika ada
                    if ($request->hasFile("attributes.{$index}.image")) {
                        $image = $request->file("attributes.{$index}.image");
                        $imagePath = $image->store('product-attributes', 'public');
                        
                        // Delete old image jika update existing attribute
                        if (isset($attributeData['id'])) {
                            $oldAttr = ProductAttribute::find($attributeData['id']);
                            if ($oldAttr && $oldAttr->image && Storage::disk('public')->exists($oldAttr->image)) {
                                Storage::disk('public')->delete($oldAttr->image);
                            }
                        }
                    }
                    
                    if (isset($attributeData['id'])) {
                        // Update existing attribute
                        $attribute = ProductAttribute::find($attributeData['id']);
                        if ($attribute && $attribute->id_product == $product->id) {
                            $updateData = [
                                'type' => $attributeData['type'],
                                'name' => $attributeData['name'],
                                'price_adjustment' => $attributeData['price_adjustment'] ?? 0,
                                'qty' => $attributeData['qty'] ?? 0,
                            ];
                            
                            if ($imagePath) {
                                $updateData['image'] = $imagePath;
                                $updateData['path'] = $imagePath;
                            }
                            
                            $attribute->update($updateData);
                            $existingAttributeIds[] = $attribute->id;
                        }
                    } else {
                        // Create new attribute
                        $newAttribute = $product->attributes()->create([
                            'type' => $attributeData['type'],
                            'name' => $attributeData['name'],
                            'image' => $imagePath,
                            'path' => $imagePath,
                            'price_adjustment' => $attributeData['price_adjustment'] ?? 0,
                            'qty' => $attributeData['qty'] ?? 0,
                        ]);
                        $existingAttributeIds[] = $newAttribute->id;
                    }
                }
            }

            DB::commit();
            
            $product->load(['category', 'attributes']);
            return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to update product.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/products/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        DB::beginTransaction();
        try {
            // Hapus semua images dari attributes
            $attributes = $product->attributes;
            foreach ($attributes as $attribute) {
                if ($attribute->image && Storage::disk('public')->exists($attribute->image)) {
                    Storage::disk('public')->delete($attribute->image);
                }
            }
            
            // Hapus attributes terkait
            $product->attributes()->delete();
            
            // Hapus product
            $product->delete();
            
            DB::commit();
            
            return $this->sendResponse([], 'Product deleted successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Failed to delete product.', ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Toggle product active status.
     * PATCH /api/products/{id}/toggle-active
     */
    public function toggleActive(string $id): JsonResponse
    {
        $product = Product::find($id);

        if (!$product) {
            return $this->sendError('Product not found.', [], 404);
        }

        try {
            $product->update(['is_active' => !$product->is_active]);
            
            $product->load(['category', 'attributes']);
            
            $message = $product->is_active ? 'Product activated successfully.' : 'Product deactivated successfully.';
            return $this->sendResponse(new ProductResource($product), $message);
            
        } catch (\Exception $e) {
            return $this->sendError('Failed to toggle product status.', ['error' => $e->getMessage()], 500);
        }
    }
}