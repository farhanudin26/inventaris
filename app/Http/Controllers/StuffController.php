<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpParser\Node\Stmt\Catch_;

class StuffController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
        {
            try {
                //ambil data yg mau ditampilkan
                $data = Stuff::all()->toArray();

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
                    'name' => 'required|min:3',
                    'category' => 'required',
                ]);

                $prosesData = Stuff::create([
                    'name' => $request->name,
                    'category' => $request->category,
                ]);

                if ($prosesData) {
                    return ApiFormatter::sendResponse(200, 'success', $prosesData);
                } else {
                    return ApiFormatter::sendResponse(400, 'bad request', 'gagal memproses tambah data stuff! silahkan coba lagi.');
                }
            }catch (\Exception $err) {
                return ApiFormatter::sendResponse(400,'bad request',$err->getMessage());
        }
    }

    //$id
    public function show($id)
    {
        try {
            $data = Stuff::where('id',$id)->first();
            //first() : kalau gada, tetep success data nya kosong
            //firstOrFail() : kalau gada, munculnya error
            //find() : mencari berdasarkan primary key (id)
            //where() : mencari column spesific tertentu (nama)

            return ApiFormatter::sendResponse(200,'success',$data);
        }catch(\Exception $err) {
            return ApiFormatter::sendResponse(400,'bad request',$err->getMessage());
    }
  }

  //Request  : data yang dikirim
  // $id : data yang akan di update, dari route{}
  public function update(Request $request,$id)
  {
    try {
        $this->validate($request, [
            'name' => 'required',
            'category' => 'required',
        ]);

        $checkProsess = Stuff::where('id',$id)->update([
            'name'=>$request->name,
            'category'=>$request->category,
        ]);

        if($checkProsess){
            // ::create([]) : menghasilkan data yang ditambah
            // ::create([]) : menghasikan boolean, jadi buat ambil data terbaru di cari lagi
            $data = Stuff::where('id',$id)->first();
            return ApiFormatter::sendResponse(200, 'succes', $data);
            }
        } catch(\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
{
    try {
        $stuff = Stuff::where('id', $id)->first();

        if ($stuff->inboundStuffs()->exists() || $stuff->stuffStock()->exists() || $stuff->lendings()->exists()) {
            return ApiFormatter::sendResponse(400, 'bad request','Tidak dapat menghapus data stuff karena sudah terdapat data inbound!');
        }

        $checkProcess = $stuff->delete();
        if ($checkProcess) {
            return ApiFormatter::sendResponse(200, 'success', 'Berhasil hapus data stuff');
        }
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}


    public function trash()
    {
        try{
        //onlyTrashed() : memanggil data sampah/yang sudah dihapus/deleted_at nya terisi
        $data = Stuff::onlyTrashed()->get();
        return ApiFormatter::sendResponse(200, 'succes', $data);
    }catch(\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
  }

  public function restore($id)
  {
    try{
        //restore : mengembalikan data spesifik yang dihapus/menghapus deleted_at nya
        $checkRestore = Stuff::onlyTrashed()->where('id',$id)->restore();

        if ($checkRestore) {
            $data = Stuff::where('id',$id)->first();
            return ApiFormatter::sendResponse(200,'success',$data);
        }
    }catch(\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
  }

  public function permanenDelete($id)
  {
    try {
        // forceDelete() : menghapus permanent (hilang juga data di db nya)
        $checkPermanenDelete = Stuff::onlyTrashed()->where('id', $id)->forceDelete();
        if ($checkPermanenDelete){
            return ApiFormatter::sendResponse(200,'success','Berhasil menghapus permanent data stuff!');
        }
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400,'bad request',$err->getMessage());
    }
  }
}
