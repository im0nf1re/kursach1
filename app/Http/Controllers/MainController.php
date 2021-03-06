<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\PaymentRequest;
use Illuminate\Http\Request;
use App\Models\PayerStatus;
use App\Models\PaymentName;
use App\Models\PaymentType;
use App\Models\Kbk;
use Illuminate\Support\Facades\Auth;

class MainController extends Controller
{
    public function index() {
        //dd(Auth::user()->role()->first()->name);
        //$this->insertIntoKbk();
        //dd($this->generateKbkNumber());

        //$banks = Bank::all();
        //dd($banks->toArray());
        return view('main');
    }

    private function generateKbkNumber()
    {
        $str = '182';
        $strTail = str_shuffle('012345678910121213');

        return $str . $strTail;
    }

    private function insertIntoKbk()
    {
        ini_set('max_execution_time', 1800);
        $paymentNames = PaymentName::all();
        $paymentTypes = PaymentType::all();

        foreach ($paymentNames as $paymentName)
        {
            foreach ($paymentTypes as $paymentType)
            {
                $kbk = new Kbk();
                $kbk->payment_name_id = $paymentName->id;
                $kbk->payment_type_id = $paymentType->id;
                $kbk->number = $this->generateKbkNumber();
                $kbk->save();
            }
        }
    }
}
