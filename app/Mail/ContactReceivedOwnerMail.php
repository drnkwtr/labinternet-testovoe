<?php

declare(strict_types=1);

namespace App\Mail;

use App\DTO\Ai\GeneratedQuoteDTO;
use App\DTO\Contact\ContactDTO;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactReceivedOwnerMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly ContactDTO $contact,
        public readonly GeneratedQuoteDTO $quote,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Новое обращение с сайта — '.$this->contact->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact.owner',
            with: ['contact' => $this->contact, 'quote' => $this->quote],
        );
    }

    // can be moved into custom job for sending email, but there's no profit for our specs
    public function failed(Throwable $e): void
    {
        Log::error('Owner contact email delivery failed', [
            'contact_id' => $this->contact->id,
            'to' => config('contact.owner_email'),
            'error' => $e->getMessage(),
        ]);
    }
}
