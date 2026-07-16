<?php

declare(strict_types=1);

namespace App\Services\Contact;

use App\DTO\Ai\GeneratedQuoteDTO;
use App\DTO\Contact\ContactDTO;
use App\Mail\ContactCopyUserMail;
use App\Mail\ContactReceivedOwnerMail;
use Illuminate\Contracts\Mail\Mailer;

final readonly class ContactNotifier
{
    public function __construct(
        private Mailer $mailer,
    ) {
    }

    public function notify(ContactDTO $contact, GeneratedQuoteDTO $quote): void
    {
        $this->mailer->to(config('contact.owner_email'))
            ->queue(new ContactReceivedOwnerMail($contact, $quote));

        $this->mailer->to($contact->email)
            ->queue(new ContactCopyUserMail($contact, $quote));
    }
}
