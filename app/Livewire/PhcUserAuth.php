<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PhcUser;
use Flux\Flux;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PhcUserAuth extends Component
{

    public string $pin = '';

    public string $targetModal = '';

    public string $parameters = '';

    #[On('open-pin-modal')]
    public function setup($targetModal = '', $parameters = '')
    {
        $this->reset('pin');
        $this->resetValidation();
        $this->targetModal = $targetModal;
        $this->parameters = $parameters;

        Flux::modal('pin-auth-modal')->show();
    }

    public function authenticateUser() {
        $user = PhcUser::query()
            ->select([
                'pe.pestamp',
                'pe.nome',
                'pe.no',
                'pe2.u_rfid',
                'pe2.u_inactivo',
                'pe2.u_okcotas',
                'pe2.u_oknci',
                'pe2.u_oktrocct',
                DB::raw('ISNULL(us.iniciais, \'\') AS iniciais'),
                DB::raw('ISNULL(us.usercode, pe.nome) AS usercode'),
                DB::raw('ISNULL(us.username, pe.nome) AS username')
            ])
            ->join('pe', 'pe.pestamp', '=', 'us.pestamp')
            ->join('pe2', 'pe2.pe2stamp', '=', 'pe.pestamp')
            ->where('pe2.u_rfid', $this->pin)
            ->where('pe2.u_inactivo', 0)->first()->toArray();

        if(!$user) {
            $this->addError('pin', 'PIN inválido ou utilizador inactivo.');
            $this->pin = '';
            return;
        }

        if(!empty($this->targetModal)) {
            $extraParams = [
                'user' => $user,
                'parameter' => $this->parameters
            ];

            $this->dispatch('open-' . $this->targetModal, $extraParams);
            Log::info("Opened target modal: " . $this->targetModal);
        }

        $this->dispatch('phc-logged-in', [
            'currentUser' => $user
        ]);

        Flux::modal('pin-auth-modal')->close();
    }

    public function appendToPin($value) {
        $this->resetValidation();

        if(strlen($this->pin) < 5) {
            $this->pin .= $value;
        }

    }

    public function clearPin() {
        $this->pin = '';
    }

    public function cancel() {
        $this->reset('pin');
        $this->resetValidation();
        Flux::modal('pin-auth-modal')->close();
    }

    public function backspacePin() {
        $this->pin = substr($this->pin, 0, -1);
    }

    public function render()
    {
        return view('livewire.phc-user-auth');
    }
}
