<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\WorkCenters;
use App\Models\InterruptReasons;
use Livewire\Attributes\On;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;

class WorkCenterFilterModal extends Component
{

    public $selectedWorkCenters = [];
    public $selectedInterruptReasons = [];

    public $workCenters = [];
    public $interruptReasons = [];

    #[On('open-wc-filters')]
    public function setup() {
        $this->reset(['selectedWorkCenters', 'selectedInterruptReasons']);
        $this->resetValidation();
        $this->loadData();
        Flux::modal('wc-filter-modal')->show();
    }

    public function loadData() {
        $this->workCenters = WorkCenters::query()
            ->select([
                'codct',
                'desct'
            ])
            ->where("inactivo", 0)
            ->where("noonline", 0)
            ->orderBy("codct", "asc")
            ->get()->toArray();

        $this->interruptReasons = InterruptReasons::query()
            ->where("inactivo", 0)
            ->orderBy("codigo", "asc")
            ->get()->toArray();
    }

    public function applyFilters() {
        $this->dispatch('filters-applied', [
            'selectedWorkCenters' => $this->selectedWorkCenters,
            'selectedInterruptReasons' => $this->selectedInterruptReasons
        ]);
        Flux::modal('wc-filter-modal')->close();

    }


    public function render()
    {
        return view('livewire.work-center-filter-modal');
    }
}
