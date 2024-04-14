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
                'proof_file' => 'required|image'
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
            //simpan data dari inbound yang diperlukan /akan digunakan nanti setelah delete
            $stuffId = $inboundData['stuff_id'];
            $totalInbound = $inboundData['total'];
            $inboundData->delete();

            //kurangin total_avalable sebelumnya dengan total dari inbound yang akan dihapus
            $dataStock = StuffStock::where('stuff_id', $inboundData['stuff_id'])->first();
            $total_available = (int)$inboundData['total_available'] - (int) $totalInbound;

            $minusTotalStock = $dataStock->update(['total_available'=> $total_available]);

            if ($minusTotalStock) {
                $updatedStuffWithInboundAndStock = Stuff::where('id',$stuffId)->with('inbounfStuffs','stuffStock')
                ->first();
                //delete inbound
                // $inboundData->delete();
                return ApiFormatter::sendResponse(200,'success',$updatedStuffWithInboundAndStock);
            }
        }catch (\Exception $err) {
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

    public function deletePermanent(Inbounstuff $inboundStuff, Request $request, $id)
    {
        try {
            $getInbound = Inbounstuff::onlyTrashed()->where('id',$id)->first();

            unlink(base_path('public/proof/'.$getInbound->proof_file));
            // Menghapus data dari database
            $checkProses = Inbounstuff::where('id', $id)->forceDelete();

            // Memberikan respons sukses
            return ApiFormatter::sendResponse(200, 'success', 'Data inbound-stuff berhasil dihapus permanen');
        } catch(\Exception $err) {
            // Memberikan respons error jika terjadi kesalahan
            return ApiFormatter::sendResponse(400, 'bad request', $err->getMessage());
        }
    }

    private function deleteAssociatedFile(Inbounstuff $inboundStuff)
    {
        // Mendapatkan jalur lengkap ke direktori public
        $publicPath = $_SERVER['DOCUMENT_ROOT'] . '/public/proof';


        // Menggabungkan jalur file dengan jalur direktori public
         $filePath = public_path('proof/'.$inboundStuff->proof_file);

        // Periksa apakah file ada
        if (file_exists($filePath)) {
            // Hapus file jika ada
            unlink(base_path($filePath));
        }
    }

}
