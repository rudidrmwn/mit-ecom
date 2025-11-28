<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StoreController extends Controller
{
    /**
     * Display all stores or filter by seller ID
     */
    public function index(Request $request)
    {
        try {
            $query = Store::query();

            // Filter berdasarkan id_seller jika ada
            if ($request->has('id_seller')) {
                $query->where('id_seller', $request->id_seller);
            }

            $stores = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Store has been found',
                'data' => StoreResource::collection($stores),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created store
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'id_seller' => 'required|integer|exists:users,id',
            'name' => 'required|string|max:255|unique:stores,name',
            'description' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // max 2MB
            'address' => 'required|string|max:500',
            'status' => 'required|in:active,inactive,pending'
        ], [
            'id_seller.required' => 'ID Seller wajib diisi',
            'id_seller.exists' => 'ID Seller tidak valid',
            'name.required' => 'Nama toko wajib diisi',
            'name.unique' => 'Nama toko sudah digunakan',
            'logo.image' => 'File harus berupa gambar',
            'logo.mimes' => 'Format gambar harus jpeg, png, jpg, atau gif',
            'logo.max' => 'Ukuran gambar maksimal 2MB',
            'address.required' => 'Alamat wajib diisi',
            'status.required' => 'Status wajib diisi',
            'status.in' => 'Status harus active, inactive, atau pending'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            
            // Upload logo jika ada
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $logoPath = $logo->storeAs('stores/logos', $logoName, 'public');
                $data['logo'] = $logoPath;
            }
            
            $store = Store::create($data);
            
            //update role jika udah registrasi toko
            $user = User::find($data['id_seller']);
            $user->id_role = 3;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Toko berhasil dibuat',
                'data' => new StoreResource($store)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified store
     */
    public function show($id)
    {
        try {
            $store = Store::find($id);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Detail toko berhasil diambil',
                'data' => new StoreResource($store)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update store by seller ID
     */
    public function updateStoreBySeller(Request $request)
    {
        try {
            $idSeller = $request->id_seller;
            
            if (!$idSeller) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID Seller wajib diisi'
                ], 400);
            }

            $store = Store::where('id_seller', $idSeller)->first();

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan untuk seller ini'
                ], 404);
            }

            // Validasi input
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255|unique:stores,name,' . $store->id,
                'description' => 'nullable|string|max:1000',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'address' => 'sometimes|required|string|max:500',
                'status' => 'sometimes|required|in:active,inactive,pending'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            // Upload logo baru jika ada
            if ($request->hasFile('logo')) {
                if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                    Storage::disk('public')->delete($store->logo);
                }

                $logo = $request->file('logo');
                $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $logoPath = $logo->storeAs('stores/logos', $logoName, 'public');
                $data['logo'] = $logoPath;
            }

            $store->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Toko berhasil diupdate',
                'data' => new StoreResource($store)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified store
     */
    public function destroy($id)
    {
        try {
            $store = Store::find($id);

            if (!$store) {
                return response()->json([
                    'success' => false,
                    'message' => 'Toko tidak ditemukan'
                ], 404);
            }

            // Hapus logo jika ada
            if ($store->logo && Storage::disk('public')->exists($store->logo)) {
                Storage::disk('public')->delete($store->logo);
            }

            $store->delete();

            return response()->json([
                'success' => true,
                'message' => 'Toko berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus toko',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}