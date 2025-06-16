<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailSend extends Mailable
{
    use Queueable, SerializesModels;

    public $mail;

    /**
     * Create a new message instance.
     */
    public function __construct($mail)
    {
        $this->mail = $mail;
    }

    public function build()
    {
        return $this->subject(subject: 'Oceana Corporation')
                    ->view('mailtemplate');
    }

    public function attachments(): array
    {
        return [];
    }
}
