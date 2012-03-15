<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $done = false;
    $error = false;

    $.get('OneClickSSL/order.raw?d={{ domain }}&v={{ voucher }}&o={{ email }}&r={{ revoke }}', function(data) {
        $('#debug').text(data).html();
        $done = true;
    })
    .error(function() { alert("{{ LANG::OrderError }}"); })
    .complete(function() { $done = true; });
    
    var imgBusy  = 'OneClickSSL/images/loading.gif';
    var imgDone  = 'OneClickSSL/images/checkmark.png';
    var imgError = 'OneClickSSL/images/errormark.png';
    
    var translation=new Array();
	translation['checkIp']       = "{{ LANG::CheckIp }}";
	translation['revokeCert']    = "{{ LANG::RevokeCert }}";
	translation['checkVoucher']  = "{{ LANG::CheckVoucher }}";
	translation['doBackup']      = "{{ LANG::DoBackup }}";
	translation['restoreBackup'] = "{{ LANG::RollingBack }}";
	translation['newPrivateKey'] = "{{ LANG::GenCSR }}";
	translation['testOrder']     = "{{ LANG::SendingTemp }}";
	translation['installTest']   = "{{ LANG::AssigningTemporary }}";
	translation['realOrder']     = "{{ LANG::ValidatingCertInstallation }}, {{ LANG::SendingProd }}";
	translation['installReal']   = "{{ LANG::AssigningProduction }}";
	translation['orderDone']     = "{{ LANG::ProcessComplete }}";
	translation['revokeDone']    = "{{ LANG::CertRevoked }}";
	
	function getStatus() {
        $.getJSON('OneClickSSL/status.raw?d={{ domain }}', function(data) {
            $.each(data, function(key, val) {
            	if (key != 'debug' && key != 'timestamp' && key != 'lastupdate') {
            		$('#ocStatus').clone().attr("id",key).appendTo('#ocWorking');
            		$('#' + key + ' .ocDsc').text(translation[key]);
            	
                	if (val == 'done') {
                    	$('#' + key + ' .ocStatusImg').attr("src",imgDone);
               		} else if (val == 'error') {
               			$error = true;
                    	$('#' + key + ' .ocStatusImg').attr("src",imgError);
                	} else {
                    	$('#' + key + ' .ocStatusImg').attr("src",imgBusy);
                	}

                    $('#' + key).fadeIn('slow');
                }

                if (key == 'debug' && $done != true) {
                    $('#debug').html(val);
                }
            });
			
			if ($error != true && $done == true) {
				$('#ocDoneNoErrors').fadeIn('slow');
			}
			
        })
        .error(function() { alert("{{ LANG::StatusError }}"); });
        
        if ($done != true) {
            var t=setTimeout(getStatus,5000);
        }
    }
    
    var t=setTimeout(getStatus,3000);
});
</script>
<style type="text/css">
{% include "style.css" %}
</style>

<div class="ocContent">
<h1>{{ LANG::ProcessingTitle }}</h1>
<p class='OCdescription'>{{ LANG::ProcessingDescription }}</p>

<div id='ocWorking' class='ocHighlight'>

  <div id="ocStatus" class="ocStatus">
    <div class="ocDsc">...</div>
    <div class="ocImg"><img class="ocStatusImg" src="" alt=""></div>
  </div>

</div>

<div id="ocDoneNoErrors">
  <b><a href="https://{{ domain }}" target="_blank">{{ LANG::TestWindow }}</a></b>
</div>

<div id="ocDebug">
  <b>{{ LANG::ShowDebug }} <input type="checkbox" onclick="$('#debug').fadeToggle('slow');"></b>
  <div id="debug"></div>
</div>
</div>