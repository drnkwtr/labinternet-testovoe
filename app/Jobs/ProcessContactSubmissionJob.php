<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTO\Contact\ContactDTO;
use App\DTO\Contact\StoreContactDTO;
use App\Repositories\ContactRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessContactSubmissionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public function __construct(
        private readonly StoreContactDTO $dto,
    ) {
    }

    public function handle(ContactRepository $repository): void
    {
        /** @var ContactDTO $contact */
        $contact = $repository->store($this->dto);

        Log::info('Contact submission stored', [
            'contact_id' => $contact->id,
            'email' => $contact->email,
        ]);
    }
}
