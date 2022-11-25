<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Mail;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class MailController extends Home_Controller {
   public function basic_email() {
      $data = array('name'=>"KanisaLink System");
   
      Mail::send(['text'=>'mail'], $data, function($message) {
         $message->to('contact@kanisalink.com', 'Tutorials Point')->subject
            ('Laravel Basic Testing Mail');
         $message->from('contact@kanisalink.com','KanisaLink System');
      });
      echo "Basic Email Sent. Check your inbox.";
   }

   public function email1() {
      $data = array('name'=>"KanisaLink System");
      Mail::send('mail', $data, function($message) {
         $message->to('albogastkiyogoma@gmail.com', 'Albogast Kiyogoma')->subject
            ('KanisaLink a Church Management System');
         $message->from('contact@kanisalink.com','KanisaLink System');
      });
      echo "HTML Email Sent. Check your inbox.";
   }

   public function email2() {
  
      $data = array('name'=>"KanisaLink System");
      Mail::send('mail2', $data, function($message) {
         $message->to('moseskalumanga@gmail.com', 'Albogast Kiyogoma')->subject
            ('KanisaLink a Church Management System');
         $message->from('contact@kanisalink.com','KanisaLink System');
      });
      echo "HTML Email Sent. Check your inbox.";
   }

}