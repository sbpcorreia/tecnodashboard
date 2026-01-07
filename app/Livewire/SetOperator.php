<?php

namespace App\Livewire;

use App\Models\Employees;
use App\Models\TouchLog;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\On;

class SetOperator extends Component
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
    public string $currentPeStamp = '';

    public string $currentEmployee = '';
    public string $resp = '';
    public string $bistamp = '';
    public string $terminal = '';
    public string $lot = '';

    public string $operator = '';

    public $operators = [];

    #[On('open-set-operator')]
    public function updateOperator($parameters) {


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
                'u_logtouch.pestamp'
            ])
            ->join("u_tabofop", "u_tabofop.u_tabofopstamp", "=", "u_logtouch.tabofopstamp")
            ->join("u_tabof", "u_tabof.u_tabofstamp", "=", "u_tabofop.u_tabofstamp")
            ->join("u_tabct", "u_tabct.codct", "=", "u_logtouch.codct")
            ->where("u_logtouchstamp", $this->logTouchStamp)->first();

        if(!$record) {
            Flux::toast("Ocorreu um erro ao abrir a janela!", "Erro", 2000, "error");
            return;
        }


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
        $this->currentPeStamp = $record->pestamp;

        $currentEmployee = Employees::query()
                ->where("pestamp", "=", $this->currentPeStamp)
                ->get()
                ->first();

        if(!$currentEmployee) {
            Flux::toast("Não foi possível carregar os dados do funcionário atual!", "Erro", 2000, "error");
            return;
        }

        $this->currentEmployee = $currentEmployee->nome;

        $operators = Employees::query()
            ->where("status", "=", 1)
            ->where("area", "=", "Tecnolanema")
            ->where("pestamp", "<>", $this->currentPeStamp)
            ->orderBy("nome", "asc")
            ->get();

        if(!$operators) {
            Flux::toast("Ocorreu um erro ao carregar os empregados!", "Erro", 2000, "error");
            return;
        }

        $this->operators = $operators->toArray();

        //dd($this->operators);
        Flux::modal('set-operator-modal')->show();

    }

    public function setNewOperator() : void {
        $operator = Employees::query()
            ->where("pestamp", "=", $this->operator)
            ->value('pestamp');

        if(!$operator) {
            Flux::toast("Operador não encontrado!", "Erro", 2000, "error");
            return;
        }

        $employee = Employees::query()
            ->where("pestamp", "=", $this->operator)
            ->get()
            ->first();

        $updated = DB::table('u_logtouch')
            ->where('u_logtouchstamp', $this->logTouchStamp)
            ->update([
                'pestamp' => $this->operator,
                'usrdata' => date('Y-m-d')
            ]);

        if(!$updated) {
            Flux::toast("Ocorreu um erro ao atualizar o registo de produção!", "Erro", 2000, "error");
        } else {
            Flux::toast("Registo de produção atualizado", "Sucesso", 2000, "success");
        }

        $this->dispatch("update-work-center", []);

        Flux::modal("set-operator-modal")->close();

    }


    public function render()
    {
        return view('livewire.set-operator');
    }
}
