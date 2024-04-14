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
}
