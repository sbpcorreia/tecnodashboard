<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WorkCenters;
use App\Models\InterruptReasons;
use Livewire\Attributes\On;
use Flux\Flux;
use Illuminate\View\View;
class WorkCenterFilterModal extends Component
{

    public $selectedWorkCenters = [];
    public $selectedInterruptReasons = [];

    public $workCenters = [];
    public $interruptReasons = [];

    #[On('open-wc-filters')]
    public function setup() : void {
        $this->reset(['selectedWorkCenters', 'selectedInterruptReasons']);
        $this->resetValidation();
        $this->loadData();
        Flux::modal('wc-filter-modal')->show();
    }

    public function loadData() : void {
        $this->workCenters = WorkCenters::query()
            ->select([
                'codct',
                'desct'
            ])
            ->where("inactivo", 0)
            ->where("noonline", 0)
            ->orderBy("codct")
            ->get()->toArray();

        $this->interruptReasons = InterruptReasons::query()
            ->where("inactivo", 0)
            ->orderBy("codigo")
            ->get()->toArray();
    }

    public function applyFilters() : void {
        $this->dispatch('filters-applied', [
            'selectedWorkCenters' => $this->selectedWorkCenters,
            'selectedInterruptReasons' => $this->selectedInterruptReasons
        ]);
        Flux::modal('wc-filter-modal')->close();

    }


    public function render() : View
    {
        return view('livewire.work-center-filter-modal');
    }
}
