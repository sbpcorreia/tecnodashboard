<?php

namespace App\Livewire;

use App\Models\TouchLog;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;
use Carbon\Carbon;
use Throwable;

#[Title("Centros de trabalho - Tecnolanema")]
class WorkCenter extends BaseComponent
{

    public $selectedWorkCenters = [];
    public $selectedInterruptReasons = [];
    public $filteredData = false;
    public $messages = [];

    #[On('filters-applied')]
    public function applyFilters($events) : void {
        $this->selectedWorkCenters = $events["selectedWorkCenters"] ?? [];
        $this->selectedInterruptReasons = $events["selectedInterruptReasons"] ?? [];
        $this->filteredData = true;

        Flux::toast("Filtro aplicado", "Sucesso", 2000, "success");
    }

    public function clearFilters() : void {
        $this->selectedWorkCenters = [];
        $this->selectedInterruptReasons = [];
        $this->filteredData = false;
        Flux::toast("Filtros removidos", "Aviso", 2000, "warning");
    }

    public function updateWorkCenter($event) : void {
        $id = $event['workCenter']['id'];
        $this->interruptedWorkCenters[$id] = $event['workCenter'];
        Flux::toast("Painel actualizado", "Sucesso", "success", "top-right");
    }

    #[Computed]
    public function interruptedWorkCenters() : Collection
    {
        $this->reset(['messages']);
        $query = TouchLog::query()
            ->select([
                DB::raw('u_logtouch.codct + u_logtouch.tabofopstamp AS id'),
                'u_logtouch.u_logtouchstamp',
                'u_logtouch.tipo',
                'u_logtouch.responsavel',
                DB::raw('u_logtouch.datareg + u_logtouch.horareg AS datahora'),
                'u_tabct.u_tabctstamp',
                'u_tabct.codct',
                'u_tabct.desct',
                'u_tabof.u_tabofstamp',
                'u_tabof.numof',
                'u_tabofop.u_tabofopstamp',
                'u_tabofop.numop',
                DB::raw('u_tabofop.descricao AS descop'),
                'u_tabpr.codigo AS codpr',
                'u_tabpr.descricao AS motivo',
                'u_tabof.ref',
                'u_tabof.design',
                DB::raw('ISNULL(u_tabpr.oculto, 0) AS oculto'),
                'pe.nome',
                DB::raw('ROW_NUMBER() OVER (
                    PARTITION BY u_tabct.u_tabctstamp, u_tabofop.u_tabofopstamp
                    ORDER BY u_logtouch.datareg + u_logtouch.horareg DESC, u_logtouch.u_logtouchstamp DESC
                ) AS rn')
            ])
            ->join("u_tabct", "u_tabct.codct", "=", "u_logtouch.codct")
            ->join("u_tabofop", function($join) {
                $join->on("u_tabofop.u_tabofopstamp", "=", "u_logtouch.tabofopstamp")
                     ->where("u_tabofop.idto", "<>", 5);
            })
            ->join("u_tabof", function($join) {
                $join->on("u_tabof.u_tabofstamp", "=", "u_tabofop.u_tabofstamp")
                     ->where("u_tabof.idto", "<>", 5);
            })
            // LEFT JOIN: os registos de arranque/fim (tipo 1 e 3) não têm motivo nem,
            // por vezes, operador. Com INNER JOIN eram excluídos do ROW_NUMBER e a
            // última paragem continuava a aparecer depois de a operação ter retomado.
            ->leftJoin("u_tabpr", "u_tabpr.u_tabprstamp", "=", "u_logtouch.tabprstamp")
            ->leftJoin("pe", "pe.pestamp", "=", "u_logtouch.pestamp")
            // Só os registos que mudam o estado da operação: 1 = início, 2 = paragem, 3 = fim
            ->whereIn("u_logtouch.tipo", [1, 2, 3])
            ->where("u_tabct.inactivo", 0)
            ->where("u_tabct.noonline", 0);

        // Os filtros são aplicados no SQL Server: fica apenas o último registo de cada
        // operação (rn = 1) e só se esse último registo for uma paragem (tipo = 2).
        $interruptedOperations = DB::query()
            ->fromSub($query, 't')
            ->where('t.rn', 1)
            ->where('t.tipo', 2)
            ->where('t.oculto', 0)
            ->when(!empty($this->selectedWorkCenters), fn ($q) => $q->whereIn('t.codct', $this->selectedWorkCenters))
            ->when(!empty($this->selectedInterruptReasons), fn ($q) => $q->whereIn('t.codpr', $this->selectedInterruptReasons))
            ->orderBy('t.codct')
            ->get();


        $interruptedOperations = $interruptedOperations->map(function ($operation) {
            $format = 'Y-m-d H:i:s.000';

            try {
                $stoppedAtCarbon = Carbon::createFromFormat($format, $operation->datahora);
                $operation->stopped_at = $stoppedAtCarbon->timestamp * 1000;

            } catch (Throwable $th) {
                $operation->stopped_at = 0;
            }
            return $operation;
        });

        return $interruptedOperations;
    }

    #[On('update-work-center')]
    public function refreshWorkCenters($params = []) : void {
        // Limpa a cache da computed property para forçar re-consulta na base de dados
        unset($this->interruptedWorkCenters);
        Flux::toast("Painel atualizado", "Info", 2000, "success", "top-right");
        // O Livewire encarrega-se de renderizar novamente
    }


    public function render() : \Illuminate\View\View
    {
        return view('livewire.work-center', [
            'interruptedWorkCenters' => $this->interruptedWorkCenters,
            // Intervalo da atualização automática (config/dashboard.php), mínimo 15 s
            'refreshSeconds' => max(15, (int) config('dashboard.refresh_seconds', 300)),
        ]);
    }
}
