<?php

namespace App\Http\Controllers;
use App\Models\Ban;
use App\Models\ChiTietHoaDonBanHang;
use App\Models\HoaDonBanHang;
use App\Models\MonAn;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DichVuController extends Controller
{
    public function getdataTheoKhuVuc(Request $request)
    {
        $data=Ban::where('status',1)
                    ->where('id_area',$request ->id)
                    ->get();
        if($data)
        {
            return response()->json([
                'data'=>$data,
            ]);
        }
    }

    public function createHoaDon(Request $request)
    {
        $ban = Ban::find($request->id_ban);
        if($ban && $ban->status == 1 && $ban->is_open_table == 0) {
            $ban->is_open_table = 1;
            $ban->save();

            return $this->createModel($request, HoaDonBanHang::class, ['message' => 'Open table success!']);
        } else {
            return response()->json([
                'status'    => 0,
                'message'   => 'The table cannot be opened!',
            ]);
        }
    }

    public function getIdHoaDon(Request $request)
    {
        $hoa_don = HoaDonBanHang::where('id_ban', $request->id_ban)
                                ->where('is_done', 0)
                                ->first();
        return response()->json([
            'status'        => 1,
            'hoa_don'    => $hoa_don,
        ]);
    }

    public function themMonAn(Request $request)
    {
        $hoa_don = HoaDonBanHang::find($request->id_hoa_don);
        if($hoa_don->is_done) {
            return response()->json([
                'status'    => 0,
                'message'   => 'Bill paid!',
            ]);
        } else {
            $monAn = MonAn::find($request->id_mon_an);
            $check = ChiTietHoaDonBanHang::where('id_hoa_don', $request->id_hoa_don)
                                            ->where('id_mon_an', $request->id_mon_an)
                                            ->first();
            if($check) {
                $check->so_luong = $check->so_luong + 1;
                $check->thanh_tien = ($check->so_luong * $check->don_gia) * (1 - ($check->phan_tram_giam/ 100));
                $check->save();
                $data=ChiTietHoaDonBanHang::where('id_hoa_don',$request->id_hoa_don)->get();
                $tong_tien=0;
                foreach($data as $key =>$value){
                    $tong_tien=$tong_tien+ $value->thanh_tien;
                }
                $hoa_don->tong_tien_truoc_giam = $tong_tien;
                $hoa_don->tien_thuc_nhan =  $tong_tien * (1 - $hoa_don->phan_tram_giam);
                $hoa_don->save();
                return response()->json([
                    'status'    => 1,
                    'message'   => 'Update successfully!',
                ]);
            } else {
                ChiTietHoaDonBanHang::create([
                    'id_hoa_don'      =>  $request->id_hoa_don,
                    'id_mon_an'       =>  $request->id_mon_an,
                    'so_luong'        =>  1,
                    'don_gia'         =>  $monAn->price,
                    'thanh_tien'      =>  $monAn->price,
                ]);


                $data=ChiTietHoaDonBanHang::where('id_hoa_don',$request->id_hoa_don)->get();
                $tong_tien=0;
                foreach($data as $key =>$value){
                    $tong_tien=$tong_tien+ $value->thanh_tien;
                }

                $hoa_don->tong_tien_truoc_giam = $tong_tien;
                $hoa_don->tien_thuc_nhan =  $tong_tien * (1 - $hoa_don->phan_tram_giam);
                $hoa_don->save();
            }
            return response()->json([
                'status'    => 1,
                'message'   => 'Item added successfully!',
            ]);
        }
    }
    public function getChiTietBanHang(Request $request)
    {
        $chiTiet=ChiTietHoaDonBanHang::join('mon_ans','mon_ans.id','chi_tiet_hoa_don_ban_hangs.id_mon_an')
                                    ->where('chi_tiet_hoa_don_ban_hangs.id_hoa_don',$request->id_hoa_don)
                                    ->where('chi_tiet_hoa_don_ban_hangs.is_done',0)
                                    ->select('chi_tiet_hoa_don_ban_hangs.*','mon_ans.food_name')
                                    ->get();
        return response()->json([
            'data'=>$chiTiet,
        ]);
    }
    public function updateChiTietBanHang(Request $request)
    {
        $chiTiet=ChiTietHoaDonBanHang::where('id',$request->id)->where('id_hoa_don', $request->id_hoa_don)->first();
        if($chiTiet)
        {
            $chiTiet->so_luong=$request->so_luong;
            $chiTiet->don_gia=$request->don_gia;
            $chiTiet->phan_tram_giam=$request->phan_tram_giam;
            $chiTiet->ghi_chu=$request->ghi_chu;
            $chiTiet->thanh_tien = ($chiTiet->so_luong * $chiTiet->don_gia) - ($chiTiet->so_luong * $chiTiet->don_gia / 100 * $chiTiet->phan_tram_giam);
            $chiTiet->update();
            $data=ChiTietHoaDonBanHang::where('id_hoa_don',$chiTiet->id_hoa_don)->get();
            $tong_tien=0;

            foreach($data as $key =>$value){
                $tong_tien=$tong_tien+ $value->thanh_tien;
            }

            $hoa_don = HoaDonBanHang::find($chiTiet->id_hoa_don);
            $hoa_don->tong_tien_truoc_giam = $tong_tien;
            $hoa_don->tien_thuc_nhan =  $tong_tien * (1 - $hoa_don->phan_tram_giam);
            $hoa_don->save();

            return response()->json([
                'status'    => 1,
                'message'   => 'Update successfully!',
            ]);

        }
    }
    public function xoaChiTietBanHang(Request $request)
    {
        $chiTiet=ChiTietHoaDonBanHang::where('id',$request->id)->first();
        if($chiTiet)
        {
            $chiTiet->delete();
            $data=ChiTietHoaDonBanHang::where('id_hoa_don',$chiTiet->id_hoa_don)->get();
            $tong_tien=0;
            foreach($data as $key =>$value){
                $tong_tien=$tong_tien+ $value->thanh_tien;
            }

            $hoa_don = HoaDonBanHang::find($chiTiet->id_hoa_don);
            $hoa_don->tong_tien_truoc_giam = $tong_tien;
            $hoa_don->tien_thuc_nhan =  $tong_tien * (1 - $hoa_don->phan_tram_giam);
            $hoa_don->save();

            return response()->json([
                'status'    => 1,
                'message'   => 'Delete successfully!',
                'tong_tien'=>$tong_tien
            ]);
        }
    }
}