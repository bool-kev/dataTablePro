<?php

namespace App\Livewire;

use App\Repositories\ImportHistoryRepository;
use Livewire\Component;
use Livewire\WithPagination;

class ImportHistory extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $showDetails = null;

    public function viewDetails($importId)
    {
        $this->showDetails = $this->showDetails === $importId ? null : $importId;
    }

    public function render()
    {
        $repository = app(ImportHistoryRepository::class);
        
        return view('livewire.import-history', [
            'imports' => $repository->paginate($this->perPage),
            'statistics' => $repository->getStatistics(),
        ]);
    }
}
