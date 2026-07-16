<?php

declare(strict_types=1);

namespace App\Mail;

use App\DTO\Contact\ContactDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactCopyUserMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ContactDTO $contact,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Мы получили ваше обращение',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.user',
            with: ['contact' => $this->contact],
        );
    }

    // same as for OwnerMail, can be moved into custom job
    public function failed(Throwable $e): void
    {
        Log::error('User contact copy email delivery failed', [
            'contact_id' => $this->contact->id,
            'to' => $this->contact->email,
            'error' => $e->getMessage(),
        ]);
    }
}
