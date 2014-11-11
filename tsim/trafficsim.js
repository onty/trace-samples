var session_id=0;
var lines_done=0;

function getSessionData(obj,input_session) {
  //Get a finished session
  if (obj) {
    $(".selected").removeClass('selected');
    obj.className="selected";
  }
  
  lines_done=0;
  lines_done=0;
  session_id=generateFakeGUID();
  $.post("ajax_request.php?sessionID="+session_id+"&session="+input_session+"&lines_done="+lines_done,"",function(data) {
    document.getElementById("ongoing_call").style.display = 'none';
    data=data.replace(/SessionID: [\w-]*;\s*/,"");
    document.getElementById("result").innerHTML="Session data.\n"+fix_avp_response(data);
  });
  return false;
}

function startSimulation() {
  //Start and follow a new simulation
  //Generate a "unique" id to use as session id
  session_id=generateFakeGUID();
  $("body").css("cursor", "progress");
  $("#result").css("cursor", "progress");
  $("input[type=submit]").attr("disabled", "disabled");
  lines_done=0;
  $("#resultsummarySent").empty();
  $("#resultsummaryOK").empty();
  $("#resultsummaryFailed").empty();
  document.getElementById("result").innerHTML="Simulating traffic case ...<br/>";
  document.getElementById("ongoing_call").style.display = 'block';
  $.post("ajax_request.php?sessionID="+session_id+generate_parameter_string(),"",function(data) {handle_response(data);});
  $.cookie("A-Number", $('input[name="A-Number"]').val(), { expires: 7, path: '/' });
  $.cookie("B-Number", $('input[name="B-Number"]').val(), { expires: 7, path: '/' });
  return false;
}

function handle_response (data) {
  //alert(data);
  var regex=/cipipsim_\d*_\d*_\d*_\d*\.log/;
  var session_id_regex=/SessionID: ([\w-]*);/;
  var local_session=regex.exec(data);
  var returned_session_id=session_id_regex.exec(data);
  if (returned_session_id != null && returned_session_id[1] != null) returned_session_id=returned_session_id[1];
  if ((''+returned_session_id) !== (''+session_id)) { //adding empty string is necessary or compare will fail
    //alert("returned_session_id: " +returned_session_id+"\nsession_id: "+session_id);
    return;
  }
  if (local_session == null) {
    document.getElementById("ongoing_call").style.display = 'none';
    document.getElementById("result").innerHTML="Session data.\n"+data.replace(/\n/g,"<br/>");
    $("#resultsummarySent").empty();
    $("#resultsummaryOK").empty();
    $("#resultsummaryFailed").empty();
    $("body").css("cursor", "auto");
    $("input[type=submit]").removeAttr("disabled");
    return;
  }
  //Remove session information tags
  data=data.replace(/Session: cipipsim_\d*_\d*_\d*_\d*\.log\s*/,"");
  data=data.replace(/SessionID: [\w-]*;\s*/,"");

  //Where there is real data available  
  if (data.length > 0) {
    //Get summary and add it to the top
    var regexSent=/Requests\s*sent:\s*\d*\/\d*\s*\(\d*\s*%\)\s*\(planned\s*\d*\)/i;
    var regexOK=/OK:\s*\d+/i;
    var regexFailed=/Failed:\s*\d+/i;
    if (summarySent=regexSent.exec(data)) $("#resultsummarySent").empty().append(''+summarySent); // ''+ variable was necessary or it would fail
    if (summaryOK=regexOK.exec(data)) $("#resultsummaryOK").empty().append(''+summaryOK);
    if (summaryFailed=regexFailed.exec(data)) $("#resultsummaryFailed").empty().append(''+summaryFailed);
    // Print all data to output area
    document.getElementById("result").innerHTML+=data.replace(/\n/g,"<br/>");
  }
  //Check how many lines we got, so we can request only new lines from the server next time
  lines_done+=(data.split("\n").length - 1)
  
  //If we didnt get any termination information from server we ask for more info
  var regex=/Session completed/i;
  var regex2=/No matching session found for/i;
  if (regex.test(data) == false && regex2.test(data) == false) {
    document.getElementById("ongoing_call").style.display = 'block';
    setTimeout(function(){
      $.post("ajax_request.php?sessionID="+session_id+"&session="+local_session+"&lines_done="+lines_done,"",function(data) {handle_response(data);});
    }, 100);
  } else {
    //We got the terminating information, reset stuff and enable submit button so a new call can be made
    document.getElementById("ongoing_call").style.display = 'none';
    lines_done=0;
    session_id=0;
    $("body").css("cursor", "auto");
    $("#result").css("cursor", "auto");
    $("input[type=submit]").removeAttr("disabled");
  }
}

function generateFakeGUID(){
  var result, i, j;
  result = '';
  for(j=0; j<32; j++) {
    if( j == 8 || j == 12|| j == 16|| j == 20)
    result = result + '-';
    i = Math.floor(Math.random()*16).toString(16).toUpperCase();
    result = result + i;
  }
  return result
} 

function generate_parameter_string () {
  var parameter_string="&";
  var x = document.getElementsByTagName("input");
  for(var i=0,j=x.length; i<j; i++) {
    if (x[i].name !="submit") {
      parameter_string+=x[i].name+"=\""+x[i].value+"\"&";
    }
  }
  return parameter_string;
}

function htmlspecialchars(str) {
 if (typeof(str) == "string") {
  str = str.replace(/&/g, "&amp;"); /* must do &amp; first */
  str = str.replace(/"/g, "&quot;");
  str = str.replace(/'/g, "&#039;");
  str = str.replace(/</g, "&lt;");
  str = str.replace(/>/g, "&gt;");
  }
 return str;
}

function fix_avp_response(str) {
  //return "<pre>"+htmlspecialchars(str)+"</pre>";
 if (typeof(str) == "string") {
  str = str.replace(/<\?xml.*?>/gi, "");
  str = str.replace(/<\/AVP>/gi, "</table></div></div></td></tr>");
  str = str.replace(/<(Request|Response)Data datestamp="(.*?)".*?>/gi, "<table style=\"font-size:smaller;\"><tr><th colspan=\"2\">$1 - $2</th></tr>");
  str = str.replace(/<AVP.*?code="1066".*?code-name="(.+?)".*?value="(.*?)".*?>/gi, "<tr><td class=\"param\">$1 (1066)</td><td>Not decoded</td></tr>");
  str = str.replace(/<AVP.*?code="1064".*?code-name="(.+?)".*?value="(.*?)".*?>/gi, "<tr><td class=\"param\">$1 (1064)</td><td style=\"max-width:300px;word-wrap:break-word;\">$2</td></tr>");
  str = str.replace(/<AVP.*?code="(\d*)".*?code-name="(.+?)".*?value="(.*?)".*?\/>/gi, "<tr><td class=\"param\">$2 ($1)</td><td>$3</td></tr>");
  str = str.replace(/<AVP.*?code="(\d*)".*?code-name="(.+?)".*?value="(.*?)".*?>/gi, "<tr><td colspan=\"2\"><div class=\"rounded BalloonDescBox\" style=\"margin-top:10px\"><div class=\"BalloonDescHdr\" >$2 ($1)</div><div><table>");
  str = str.replace(/<\/(Request|Response)Data>/gi, "</table>");
  }
 return str;
}

