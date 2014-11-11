<?php

$config["sim_ip"]="";

function read_config () {
  global $config;
  if (is_file("config/configuration")) {
    $lines = file("config/configuration");
    foreach($lines as $line) {
      if (preg_match('/^(\w*)[:=,](.*)/',$line,$matches)) {
        global ${$matches[1]};
        $config[$matches[1]]=$matches[2];
        $config["s_".$matches[1]]=$matches[2];
#        ${$matches[1]}=$matches[2];
        if (isset($_COOKIE[$matches[1]]) && strlen($_COOKIE[$matches[1]])) {
          $config[$matches[1]]=$_COOKIE[$matches[1]];
          $config["c_".$matches[1]]=$_COOKIE[$matches[1]];
        }
      }
    }
  }
}

$valuemap['CC-Request-Type']['1']="INITIAL_REQUEST";
$valuemap['CC-Request-Type']['2']="UPDATE_REQUEST";
$valuemap['CC-Request-Type']['3']="TERMINATION_REQUEST";
$valuemap['CC-Request-Type']['4']="EVENT_REQUEST";
$valuemap['Result-Code']['2001']="Diameter_Success";
$valuemap['Result-Code']['3002']="Diameter_Unable_To_Deliver";
$valuemap['Result-Code']['3003']="DIAMETER_REALM_NOT_SERVED";
$valuemap['Result-Code']['4002']="DIAMETER_OUT_OF_SPACE";
$valuemap['Result-Code']['4010']="Diameter_End_User_Service_Denied";
$valuemap['Result-Code']['4011']="Diameter_Credit_Control_Not_Appl.";
$valuemap['Result-Code']['4012']="Diameter_Credit_Limit_Reached";
$valuemap['Result-Code']['5001']="Diameter_AVP_Unsupported";
$valuemap['Result-Code']['5002']="Diameter_Unknown_Session_Id";
$valuemap['Result-Code']['5004']="Diameter_Invalid_AVP_Value";
$valuemap['Result-Code']['5005']="Diameter_Missing_Parameter";
$valuemap['Result-Code']['5012']="Diameter_Unable_To_Comply";
$valuemap['Result-Code']['5030']="Diameter_End_User_Unknown";
$valuemap['Result-Code']['5031']="Diameter_Rating_Failed";
$valuemap['Result-Code-Extension']['1']="UNKNOWN_SERVICE";
$valuemap['Result-Code-Extension']['2']="MAX_COST";
$valuemap['Result-Code-Extension']['3']="TOLLFREE_BY_RATING";
$valuemap['Result-Code-Extension']['4']="WRONG_CURRENCY_CODE";
$valuemap['Result-Code-Extension']['5']="RATING_FAILED";
$valuemap['Result-Code-Extension']['6']="SERVICE_INTERRUPTED_BY_RATING";
$valuemap['Result-Code-Extension']['7']="INVALID_SERVICE_DATA";
$valuemap['Result-Code-Extension']['8']="UNKNOWN_CHARGING_CONTEXT";
$valuemap['Result-Code-Extension']['9']="INCOMPATIBLE_CONSUMER_PROVIDER_SERVICE_CLASS_DATA";
$valuemap['Result-Code-Extension']['993']="SUBSCRIBER_IN_RED_LIST";
$valuemap['Service-Setup-Result']['0']="Successful - Released by service";
$valuemap['Service-Setup-Result']['1']="Successful - Disconnected by calling party";
$valuemap['Service-Setup-Result']['2']="Successful - Disconnect by called party";
$valuemap['Service-Setup-Result']['3']="Successful - Ongoing (toll free)";
$valuemap['Service-Setup-Result']['4']="Non-Successful - Called party route select failure";
$valuemap['Service-Setup-Result']['5']="Non-Successful - Called party busy";
$valuemap['Service-Setup-Result']['6']="Non-Successful - Called party not reachable";
$valuemap['Service-Setup-Result']['7']="Non-Successful - Called party no answer";
$valuemap['Service-Setup-Result']['8']="Non-Successful - Calling party abandon";
$valuemap['Service-Setup-Result']['14']="Non-Successful - Other-Reason";
$valuemap['Service-Setup-Result']['15']="Call Forwarding bas been invoked - Charging cancelled";
$valuemap['Roaming-Position']['0']="inside HPLMN";
$valuemap['Roaming-Position']['1']="outside HPLMN";
$valuemap['Service-Scenario']['0']="Mobile Originating";
$valuemap['Service-Scenario']['1']="Mobile Forwarding";
$valuemap['Service-Scenario']['2']="Mobile Terminating";
$valuemap['Final-Unit-Action']['0']="TERMINATE";
$valuemap['Final-Unit-Action']['1']="REDIRECT";
$valuemap['Final-Unit-Action']['2']="RESTRICT_ACCESS";
$help['A-Number']="Originating party MSISDN in international format, e.g. (4647xxxxxx)";
$help['B-Number']="Terminating party MSISDN in national format, e.g. (47xxxxxx)<br/>Other format can be used as long as they match what is used in SDP tariffs, FaF, Black/White lists etc.";
$help['Duration-II']="Number of seconds to be charged per intermediate interrogation";
$help['Duration-FI']="Number of seconds to be charged for final report";
$help['CC-Total-Octets']="Number of bytes to be charged";
$help['MNP-ID']="Other Party Id MNP-RN";
?>
