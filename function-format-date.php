<?php
function format_date($date)
{
    $date_first_step = stripslashes($date);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_first_step)) {
        $date_second_step = new DateTime(($date_first_step));
        $date_final = $date_second_step->format('d/m/Y');
        echo $date_final;
    }
}
