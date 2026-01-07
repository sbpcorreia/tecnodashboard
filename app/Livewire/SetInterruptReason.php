<?php

namespace App\Livewire;

use App\Models\InterruptManagers;
use Livewire\Attributes\Computed;
use Livewire\Component;
use App\Models\TouchLog;
use App\Models\InterruptReasons;
use Livewire\Attributes\On;
use Flux\Flux;
use Illuminate\Support\Facades\DB;

class SetInterruptReason extends Component
{

    public $user = [];
    public string $parameter = '';
    public string $logTouchStamp = '';

    public string $workCenterStamp = '';
    public string $workCenterCode = '';
    public string $workCenterDescription = '';

    public string $operationCode = '';
    public string $operationDescription = '';
    public string $operationStamp = '';

    public string $workOrderCode = '';
    public string $workOrderStamp = '';

    public string $resp = '';
    public string $bistamp = '';
    public string $terminal = '';
    public string $lot = '';

    public string $reason = '';

    public string $currentReason = '';
    public string $currentManager = '';
    public string $currentEmployee = '';

    //public $managers = [];
    public string $manager = '';
    //public $reasons = [];

    public function updatedReason() : void {
        $this->reset('manager');
    }

    #[On('open-set-interrupt-reason')]
    public function updateInterruptReasons($parameters) : void {
        $this->user = $parameters['user'] ?? [];
        $this->logTouchStamp = $parameters['parameter'] ?? '';

        if(empty($this->user) || empty($this->logTouchStamp)) {
            Flux::toast("Ocorreu um erro ao abrir a janela de definição do motivo de paragem!", "Erro", 2000, "error");
            return;
        }

        $record = TouchLog::query()
            ->select([
                'u_logtouch.codct',
                'u_logtouch.tabofopstamp',
                'u_tabof.numof',
                'u_tabof.u_tabofstamp',
                'u_logtouch.tabprstamp',
                DB::raw('u_tabofop.descricao AS descop'),
                'u_tabofop.numop',
                'u_tabct.u_tabctstamp',
                'u_logtouch.bistamp',
                'u_logtouch.responsavel',
                'u_logtouch.posto',
                'u_logtouch.lote',
                'u_tabpr.codigo AS codpr',
                'u_tabpr.descricao AS motivo'
            ])
            ->join("u_tabofop", "u_tabofop.u_tabofopstamp", "=", "u_logtouch.tabofopstamp")
            ->join("u_tabof", "u_tabof.u_tabofstamp", "=", "u_tabofop.u_tabofstamp")
            ->join("u_tabct", "u_tabct.codct", "=", "u_logtouch.codct")
            ->join("u_tabpr", "u_tabpr.u_tabprstamp", "=", "u_logtouch.tabprstamp")
            ->where("u_logtouchstamp", $this->logTouchStamp)->first();
        if($record) {
            $this->workCenterCode = $record->codct;
            $this->workOrderCode = $record->numof;
            $this->operationStamp = $record->tabofopstamp;
            $this->operationCode = $record->numop;
            $this->operationDescription = $record->descop;
            $this->workOrderStamp = $record->u_tabofstamp;
            $this->workCenterStamp = $record->u_tabctstamp;
            $this->resp = $record->responsavel;
            $this->bistamp = $record->bistamp;
            $this->lot = $record->lote;
            $this->terminal = $record->posto;
            $this->currentReason = $record->motivo;
            $this->currentManager = $record->responsavel;
            $this->reason = $record->codpr;
            $this->manager = $record->responsavel;
        }

        Flux::modal('set-interrupt-reason-modal')->show();
    }

    #[Computed]
    public function reasons() : array {
        $reasons = InterruptReasons::query()
            ->where("inactivo", 0)
            ->where('oculto', 0)
            ->orderBy("codigo")
            ->get();

        return $reasons->toArray();
    }


    #[Computed]
    public function managers() : array {

        $reasonObject = InterruptReasons::query()
            ->where("codigo", "=", $this->reason)
            ->first();

        $reason = $reasonObject->u_tabprstamp ?? '';

        $managers = InterruptManagers::query()
            ->where("u_tabprstamp", "=", $reason)
            ->orderBy("respnom")
            ->get();


        return $managers->toArray();
    }

    public function applyNewReasonToOperation() : void {

        $interruptReasonStamp = InterruptReasons::query()
                ->where('codigo', $this->reason)
                ->value('u_tabprstamp');

        if(!$interruptReasonStamp) {
            Flux::toast("Deve indicar um motivo de interrupção!", "Aviso", 2000, "warning");
            return;
        }

        $inserted = TouchLog::insert([
            'u_logtouchstamp' => TouchLog::newId(),
            'datareg' => date('Y-m-d'),
            'horareg' => date('H:i:s'),
            'posto' => $this->terminal,
            'quant' => 0,
            'tabofopstamp' => $this->operationStamp,
            'numof' => $this->workOrderCode,
            'codct' => $this->workCenterCode,
            'pestamp' => $this->user['pestamp'] ?? '',
            'tipo' => 2,
            'etiquetastamp' => '',
            'bistamp' => $this->bistamp,
            'seiki' => 1,
            'tabprstamp' => $interruptReasonStamp,
            'lote' => $this->lot,
            'caixa' => '',
            'ousrinis' => $this->user["iniciais"],
            'ousrdata' => date('Y-m-d'),
            'ousrhora' => date('H:i:s'),
            'usrinis' => $this->user["iniciais"],
            'usrdata' => date('Y-m-d'),
            'usrhora' => date('H:i:s'),
            'responsavel' => $this->manager
        ]);

        $message = [];
        if($inserted) {
            Flux::toast("Motivo de paragem alterado com sucesso!", "Atualizado", 2000, "success");
        } else {
            Flux::toast("Ocorreu um erro ao alterar o motivo de paragem!", "Erro", 2000, "error");
        }


        $this->dispatch('update-work-center', $message);

        Flux::modal('set-interrupt-reason-modal')->close();
    }


    public function render() : \Illuminate\View\View
    {
        return view('livewire.set-interrupt-reason');
    }
}
