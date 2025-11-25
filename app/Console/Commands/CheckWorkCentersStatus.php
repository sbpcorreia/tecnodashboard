<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TouchLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\RemoveCard;

class CheckWorkCentersStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-work-centers-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica mudanças nos logs dos centros de trabalho e dispara os eventos para o Livewire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $checkIntervalSeconds = 10;

        $startTime = now()->subSeconds($checkIntervalSeconds + 5);

        $startTimeString = $startTime->format('Y-m-d H:i:s.000');

        $results = TouchLog::query()
            ->select([
                DB::raw('u_logtouch.codct + u_logtouch.tabofopstamp AS id'),
                'u_logtouch.u_logtouchstamp',
                'u_logtouch.tipo',
                DB::raw('u_logtouch.datareg + u_logtouch.horareg AS datahora'),
                'u_tabct.u_tabctstamp',
                'u_tabct.codct',
                'u_tabct.desct',
                'u_tabof.u_tabofstamp',
                'u_tabof.numof',
                'u_tabofop.u_tabofopstamp',
                'u_tabofop.numop',
                DB::raw('u_tabofop.descricao AS descop'),
                DB::raw("ISNULL(u_tabpr.codigo, '') AS codpr"),
                DB::raw("ISNULL(u_tabpr.descricao,'') AS motivo"),
                DB::raw('u_tabofop.idto AS op_status'),
                DB::raw('u_tabof.idto AS of_status'),
                'pe.nome',
                // Nota: Window functions são pesadas. Certifique-se que tem índices em (datareg, horareg)
                DB::raw('ROW_NUMBER() OVER (
                    PARTITION BY u_tabct.u_tabctstamp, u_tabofop.u_tabofopstamp
                    ORDER BY u_logtouch.datareg DESC, u_logtouch.horareg DESC, u_tabof.u_tabofstamp DESC, u_tabofop.u_tabofopstamp
                ) AS rn')
            ])
            ->join("u_tabct", "u_tabct.codct", "=", "u_logtouch.codct")
            ->join("u_tabofop", "u_tabofop.u_tabofopstamp", "=", "u_logtouch.tabofopstamp")
            ->join("u_tabof", "u_tabof.u_tabofstamp", "=", "u_tabofopstamp.u_tabofstamp")
            ->leftJoin("u_tabpr", "u_tabpr.u_tabprstamp", "=", "u_logtouch.tabprstamp")
            ->join("pe", "pe.pestamp", "=", "u_logtouch.pestamp")
            ->where("u_tabct.inactivo", 0)
            ->where("u_tabct.noonline", 0)
            ->whereRaw("CAST(u_logtouch.datareg + ' ' + u_logtouch.horareg AS DATETIME) >= ?", $startTimeString)
            ->get();

        $lastOperationsWithStatusChange = $results->filter(function($item) {
            return $item->rn == 1;
        });

        Log::info("A obter os dados... Retornados " . $results->count() . " registos");

        $extractedOperationIds = collect();

        if(empty($results)) {
            Log::info("Não foram retornados resultados... a aguardar pela próxima execução...");
            return Command::SUCCESS;
        }

        foreach($results as $operation) {
            if(in_array([1,3], $operation->tipo) || $operation->of_status == 5 || $operation->op_status == 5) {
                $extractedOperationIds->push($operation->u_tabofopstamp);
            }
        }

        // 4. Disparar o Evento para cada ID único encontrado
        $uniqueIds = $extractedOperationIds->unique();

        foreach($uniqueIds as $id) {
            // Importar a classe do evento no topo: use App\Events\RemoveCard;
            event(new RemoveCard($id));

            Log::info("⚡ Evento RemoveCard disparado para ID: $id");
        }

        return Command::SUCCESS;


    }
}
