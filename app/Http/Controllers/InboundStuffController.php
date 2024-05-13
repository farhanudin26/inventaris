<?php

namespace App\Http\Controllers;

use App\Helpers\ApiFormatter;
use App\Models\Stuff;
use App\Models\Inbounstuff;
use App\Models\StuffStock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InboundStuffController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }


    public function index (Request $request)
    {
        try {
            if($request->filter_id) {
        $data = Inbounstuff::where('stuff_id',$request->filter_id)->with('stuff','stuff.stuffStock')->get();
    } else {
        $data = Inbounstuff::all();
    }
    return ApiFormatter::sendResponse(200, 'success', $data);
    }catch(\Exception $err){
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'stuff_id' => 'required',
                'total' => 'required',
                'date' => 'required',
                // 'proof': type file image
                'proof_file' => 'required|mimes:jpeg,png,jpg,pdf|max:2048',
             ]);

            // $request->file(): ambil data yg tipe nya file
            // getClientOriginalName(): ambil nama asli dari file yg diupload
            // Str::random(jumlah_karakter): generate random karakter sejumlah
            $namaImage = Str::random(5) . "_" . $request->file('proof_file')->getClientOriginalName();
            // move(): memindahkan file yg diupload ke folder public, dan nama file nya mau apa
            $request->file('proof_file')->move('upload-images', $namaImage);
            // ambil URL untuk menampilkan gambarnya
            $pathImage = url('upload-images/' . $namaImage);

            $inboundData = Inbounstuff::create([
                'stuff_id' => $request->stuff_id,
                'total' => $request->total,
                'date' => $request->date,
                // yg dimasukkan ke db data lokasi URL gambarnya
                'proof_file' => $pathImage,
            ]);

            if ($inboundData) {
                $stockData = StuffStock::where('stuff_id', $request->stuff_id)->first();
                if ($stockData) { // kalau data stuffstock yg stuff_id nya kaya yg dibuat ada
                    $total_available = (int)$stockData['total_available'] + (int)$request->total; // (int) memastikan kalau dia integer, klo ga integer diubah jd integer
                    $stockData->update(['total_available' => $total_available]);
                } else { // kalau stock nya blm ada, dibuat
                    StuffStock::create([
                        'stuff_id' => $request->stuff_id,
                        'total_available' => $request->total, // total_available nya dr inputan total inbound
                        'total_defec' => 0,
                    ]);
                }
                // ambil data dr stuff, inboundstuffs, dan stuffstock dr stuff_id terkait
                $stuffWithInboundAndStock = Stuff::where('id', $request->stuff_id)->with('inboundStuffs', 'stuffStock')->first();
                return ApiFormatter::sendResponse(200, 'success', $stuffWithInboundAndStock);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $inboundData = Inbounstuff::where('id', $id)->first();
            //simpan data dari inbound yang diperlukan / akan digunakan nanti setelah delete
            $stuffId = $inboundData['stuff_id'];
            $totalInbound = $inboundData['total'];

            //kurangi total_available sebelumnya dengan total dari inbound yang akan dihapus
            $dataStock = StuffStock::where('stuff_id', $inboundData['stuff_id'])->first();

            if ($dataStock['total_available'] < $totalInbound) {
                return ApiFormatter::sendResponse(400,'bad request','Jumlah total inbound yang akan dihapus lebih besar dari total available stuff saat ini!');
            }
            $total_available = (int)$dataStock['total_available'] - (int) $totalInbound;


            // hapus data inbound
            $inboundData->delete();

            // update total_available di StuffStock
            $minusTotalStock = StuffStock::where('stuff_id', $inboundData['stuff_id'])->update(['total_available' => $total_available]);

            if ($minusTotalStock) {
                // ambil data Stuff setelah perubahan
                $updatedStuffWithInboundAndStock = Stuff::where('id', $stuffId)->with('inboundStuffs', 'stuffStock')->first();
                return ApiFormatter::sendResponse(200, 'success', $updatedStuffWithInboundAndStock);
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }


    public function trash()
    {
        try{
            $data= Inbounstuff::onlyTrashed()->get();

            return ApiFormatter::sendResponse(200, 'success', $data);
        }catch(\Exception $err){
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    public function restore(Inbounstuff $inboundStuff, $id)
    {
        try {
            // Memulihkan data dari tabel 'inbound_stuffs'
            $checkProses = Inbounstuff::onlyTrashed()->where('id', $id)->restore();

            if ($checkProses) {
                // Mendapatkan data yang dipulihkan
                $restoredData = Inbounstuff::find($id);

                // Mengambil total dari data yang dipulihkan
                $totalRestored = $restoredData->total;

                // Mendapatkan stuff_id dari data yang dipulihkan
                $stuffId = $restoredData->stuff_id;

                // Memperbarui total_available di tabel 'stuff_stocks'
                $stuffStock = StuffStock::where('stuff_id', $stuffId)->first();

                if ($stuffStock) {
                    // Menambahkan total yang dipulihkan ke total_available
                    $stuffStock->total_available += $totalRestored;

                    // Menyimpan perubahan pada stuff_stocks
                    $stuffStock->save();
                }

                return ApiFormatter::sendResponse(200, 'success', $restoredData);
            } else {
                return ApiFormatter::sendResponse(400, 'bad request', 'Gagal mengembalikan data!');
            }
        } catch (\Exception $err) {
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

   public function permanentDelete(Inbounstuff $inboundStuff, Request $request, $id)
{
    try {
        $inboundData = Inbounstuff::onlyTrashed()->findOrFail($id);
        $proofFilePath = base_path('public/proof/' . $inboundData->proof_file);

        if (file_exists($proofFilePath)) {
            unlink($proofFilePath); // Hapus file dari storage
        }

        $inboundData->forceDelete(); // Hapus data dari database secara permanen

        return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
    } catch (\Exception $err) {
        return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
    }
}


}
