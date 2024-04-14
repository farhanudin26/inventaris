<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ApiFormatter;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    public function index()
    {
        try {
            //ambil data yg mau ditampilkan
            $data = User::all()->toArray();

            return ApiFormatter::sendResponse(200, 'success', $data);
        }catch (\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function store (Request $request)
    {
        try {
            // validasi
            // 'nama_column' => validasi
            $this->validate($request, [
                'username' => 'required|min:4|unique:users,username',
                'email' => 'required|unique:users,email',
                'password' => 'required|min:6',
                'role' => 'required'
            ]);

            $prosesData = user::create([
                'email' => $request->email,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($prosesData) {
                return ApiFormatter::sendResponse(200, 'success', $prosesData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'gagal memproses tambah data users! silahkan coba lagi.');
            }
        }catch (\Exception $err) {
            return ApiFormatter::sendResponse(400,'bad request',$err->getMessage());
    }
  }

  public function show($id)
  {
      try {
          $data = User::where('id',$id)->first();
          //first() : kalau gada, tetep success data nya kosong
          //firstOrFail() : kalau gada, munculnya error
          //find() : mencari berdasarkan primary key (id)
          //where() : mencari column spesific tertentu (nama)

          return ApiFormatter::sendResponse(200,'success',$data);
      }catch(\Exception $err) {
          return ApiFormatter::sendResponse(400,'bad request',$err->getMessage());
    }
  }

  public function update(Request $Request, $id)
  {
      try {
          $this->validate($Request, [
              'username' => 'required|min:4|unique:users,username,' . $id,
              'email' => 'required|unique:users,email,' . $id,
              'password' => 'required|min:6',
              'role' => 'required'
          ]);

          $checkProses = User::where('id', $id)->update([
              'username' => $Request->username,
              'email' => $Request->email,
              'password' => hash::make($Request->password),
              'role' => $Request->role
          ]);

          if ($checkProses) {
              $data = User::where('id', $id)->first();

              return ApiFormatter::sendResponse(200, 'success', $data);
          }
      } catch (\Exception $err) {
          return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
      }
  }

    public function destroy($id)
    {
        try {
            $checkProsess = User::where('id',$id)->delete();
            if ($checkProsess) {
                return ApiFormatter::sendResponse(200, 'succes', 'Berhasil hapus data user!');
            }
        }catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function trash()
    {
        try{
        //onlyTrashed() : memanggil data sampah/yang sudah dihapus/deleted_at nya terisi
        $data = User::onlyTrashed()->get();
        return ApiFormatter::sendResponse(200, 'succes', $data);
    }catch(\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
  }

  public function restore($id)
  {
    try{
        //restore : mengembalikan data spesifik yang dihapus/menghapus deleted_at nya
        $checkRestore = User::onlyTrashed()->where('id',$id)->restore();

        if ($checkRestore) {
            $data = User::where('id',$id)->first();
            return ApiFormatter::sendResponse(200,'success',$data);
        }
    }catch(\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
  }

  public function permanenDelete($id)
  {
      try{
          $cekPermanentDelete = User::onlyTrashed()->where('id', $id)->forceDelete();

          if ($cekPermanentDelete) {
              return
              ApiFormatter::sendResponse(200, 'success','Berhasil menghapus data secara permanen' );
          }
      } catch (\Exception $err) {
          return
          ApiFormatter::sendResponse(400,'bad_request', $err->getMessage());
    }

    }

}
