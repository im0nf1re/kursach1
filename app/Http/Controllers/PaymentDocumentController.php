<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\PayerType;
use App\Models\PaymentKind;
use App\Models\PaymentType;
use App\Models\PaymentBasis;
use App\Models\Period;
use App\Models\Kbk;
use App\Models\PaymentDocument;
use App\Models\PeriodItem;

// TODO Доделать валидацию в дейтпикере
class PaymentDocumentController extends Controller
{
    public function index()
    {
        return view('main');
    }

    public function create(Request $request)
    {
        $periodItems = Period::find(1)->periodItems()->get();


        if (isset($request->target) && ($request->target == 'fl' || $request->target == 'ip')) {
            $id = $request->target == 'ip' ? 1 : 2;
            return view('pay')->with([
                'subjects' => Subject::all(),
                'id' => $id
            ]);
        }
        else
            return redirect(route('main'));
    }

    public function store(Request $request)
    {
        $periodItem = $request->periodItem;
        $year = $request->year;
        $date = $request->datepicker;
        $kbkId = $request->kbkId;
        $payerStatusId = $request->payerStatusId;
        $oktmoId = $request->oktmoId;
        $ifnsId = $request->ifnsId;
        $firstname = $request->firstname;
        $surname = $request->surname;
        $patronymic = $request->patronymic;
        $inn = $request->inn;
        $address = $request->address;

        if (
            !($kbkId && $payerStatusId
            && $oktmoId && $ifnsId && $firstname && $surname && $patronymic
            && $inn && $address)
            || !($date || ($periodItem && $year))
        )
        {
            return json_encode([
                'success' => false,
                'error_code' => 'not_enough_data',
            ]);
        }

        // составляем строку данных
        $strDate = '';
        if ($date)
        {
            $arDate = explode('-', $date);
            $strDate = $arDate[2].'.'.$arDate[1].'.'.$arDate[0];
        }
        else
        {
            $strDate = PeriodItem::find($periodItem)->name.' '.$year;
        }

        // создаем запись в таблицу payment_documents
        $paymentDocument = new PaymentDocument();
        $paymentDocument->ddate = $strDate;
        $paymentDocument->kbk_id = $kbkId;
        $paymentDocument->payer_status_id = $payerStatusId;
        $paymentDocument->oktmo_id = $oktmoId;
        $paymentDocument->ifns_id = $ifnsId;
        $paymentDocument->firstname = $firstname;
        $paymentDocument->surname = $surname;
        $paymentDocument->patronymic = $patronymic;
        $paymentDocument->inn = $inn;
        $paymentDocument->address = $address;

        $paymentDocument->save();

        return json_encode([
            'success' => true
        ]);

    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }

    // ajax functions
    public function oktmos(Request $request) {
        $oktmos = Subject::find($request->id)->oktmos()->get();

        return view('parts.oktmo')->with('oktmos', $oktmos);
    }

    public function paymentKinds(Request $request) {
        $paymentKinds = PayerType::find($request->id)->paymentKinds()->get();

        return view('parts.paymentKind')->with('paymentKinds', $paymentKinds);
    }

    public function paymentNames(Request $request) {
        $paymentNames = PaymentKind::find($request->id)->paymentNames()->get();

        return view('parts.paymentName')->with('paymentNames', $paymentNames);
    }

    public function paymentTypes(Request $request) {
        $paymentTypes = PaymentType::all();

        return view('parts.paymentType')->with('paymentTypes', $paymentTypes);
    }

    public function payerStatuses(Request $request) {
        $payerStatuses = PayerType::find($request->id)->payerStatuses()->get();

        return view('parts.payerStatus')->with('payerStatuses', $payerStatuses);
    }

    public function paymentBases(Request $request) {
        $paymentBases = PayerType::find($request->id)->paymentBases()->get();
        return view('parts.paymentBasis')->with('paymentBases', $paymentBases);
    }

    public function dataKinds(Request $request) {
        $dataKind = PaymentBasis::find($request->id)->dataKinds()->get()[0];
        return view('parts.dataKind')->with('dataKind', $dataKind);
    }

    public function periodItems(Request $request) {
        $periodItems = Period::find($request->id)->periodItems()->get();
        return view('parts.periodItem')->with(['periodItems' => $periodItems, 'periodId' => $request->id]);
    }

    public function fio() {
        return view('parts.fio');
    }

    public function ready() {
        return view('parts.ready');
    }

    public function kbk(Request $request)
    {
        $kbk = Kbk::where([
            ['payment_name_id', '=', $request->paymentNameId],
            ['payment_type_id', '=', $request->paymentTypeId],
        ])->first();

        $kbkCode = '';
        $kbkId = '';
        if ($kbk)
        {
            $kbkCode = $kbk->number;
            $kbkId = $kbk->id;
        }

        return json_encode(['code' => $kbkCode, 'id' => $kbkId]);
    }
}