<?php

namespace enrol_cielo\task;
 
/**
 * An example of a scheduled task.
 */
class checkpayments extends \core\task\scheduled_task {
 
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('checkboleto', 'enrol_cielo');
    }
 
    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        
        $recs = $DB->get_records('enrol_cielo', ['type' => 'boleto', 'payment_status' => 'pending']);
        foreach ($recs as $rec) {
            $response = checkboleto($rec);
            updaterecord($response);
            if (boletoispayed($response)) {
               //enroluser
            }
        }
    }
    
    private function checkboleto($baseurl, $rec){
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => $baseurl.'/1/sales/'.$rec->tid,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => [
            'MerchantId: '.$rec->merchantid,
            'Content-Type: text/json',
            'MerchantKey: '.$rec->merchantkey
          ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }
    
    private function boletoispayed($payment) {
    
        return true;
    }
    
    private function updaterecord($baseurl, $payment){
        global $USER, $DB;
        
        // Check if boleto is expired
        // Check if boleto is payed

        $DB->update_record("enrol_cielo", $rec);
    
    }
    
    private function handleenrolment($rec) {
        global $DB;

        $plugin = enrol_get_plugin('cielo');
        $plugininstance = $DB->get_record('enrol', array('courseid' => $rec->courseid, 'enrol' => 'cielo'));

        if ($plugininstance->enrolperiod) {
            $timestart = time();
            $timeend = $timestart + $plugininstance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }

        switch ($rec->payment_status) {
            case 'success':
                $plugin->enrol_user($plugininstance, $rec->userid, $plugininstance->roleid, $timestart, $timeend);
                break;
            case 'pending':
            case 'failure':
                $plugin->unenrol_user($plugininstance, $rec->userid);
                break;
        }
    
    }
}
