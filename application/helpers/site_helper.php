<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


      function IDPromoGenerator()
      {
        date_default_timezone_set("Asia/Bangkok");
        $date = date("ymdHis");
        $id = "PR-".$date;
        return $id;
      }

      function IDNotaGenerator()
      {
        date_default_timezone_set("Asia/Bangkok");
        $date = date("ymdHis");
        $id = "NT-".$date;
        return $id;
      }

      function IDDetailNotaGenerator()
      {
        date_default_timezone_set("Asia/Bangkok");
        $date = date("ymdHis");
        $id = "DN-".$date;
        return $id;
      }

      function IDOrderGenerator($idstan)
      {
        date_default_timezone_set("Asia/Bangkok");
        $date = date("ymdHis");
        $id = "OR-".$date."ST-".$idstan;
        return $id;
      }

      function hosturl()
      {
        // return 'http://teabreak.bekkostudio.com/';
        return 'http://localhost/teabreak/';
      }