<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentRecieved extends Mailable
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
        return $this->view('emails.paymentRecieved')->with([
            'name' => $this->data->name,
            'booking_no' => $this->data->booking_no,
            'number_of_kids' => $this->data->number_of_kids,
            'amount_per_child' => $this->data->amount_per_child,
            'total_amount' => $this->data->total_amount,
            'event_name' => $this->data->event_name,
            'village_name' => $this->data->village_name,
            'address' => $this->data->address,
            'date' => $this->data->date,
            'start_time' => $this->data->start_time,
            'end_time' => $this->data->end_time,
            'url' => $this->data->url,
            'hideme' => $this->data->hideme
        ]);
    }
}
