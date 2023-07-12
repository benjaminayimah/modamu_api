<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class YourAccountIsReady extends Mailable
{
    use Queueable, SerializesModels;
    public $data;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.adminSendLoginDetails')->with([
            'url' => $this->data->url,
            'name' => $this->data->name,
            'email' => $this->data->email,
            'password' => $this->data->password,
            'account_type' => $this->data->account_type
        ]);
    }
}
