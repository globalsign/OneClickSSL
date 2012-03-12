<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<style type="text/css">
{% include "style.css" %}
</style>

<!-- Advanced Settings -->
<div class="ocContent">
<form action="" method="post">
	<h1>GlobalSign OneClickSSL {{ LANG::FormAdvancedOptions }}</h1>
	<p>{{ LANG::FormAdvancedOptionsHelp }}</p>
	<!-- DebugLevel -->
	<h2>{{ LANG::FormShowDebugTitle }}</h2>
	<p class='OCdescription'>The bebugging level configures the amount of information the server will use when logging. The level parameter must be between 0 and 9.</p>
	<div class="ocHighlight">
		<div class="ocSetting">
		  <div class="ocDsc">Amount of information</div>
		  <div class="ocInput">
			<select name="debug_level" id="debug_level">
			  <option value="">{{ LANG::Default }}</option>
			  <option value="0">0</option>
			  <option value="1">1</option>
			  <option value="2">2</option>
			  <option value="3">3</option>
			  <option value="4">4</option>
			  <option value="5">5</option>
			  <option value="6">6</option>
			  <option value="7">7</option>
			  <option value="8">8</option>
			  <option value="9">9</option>
			</select>
			<script type="text/javascript">
				$(document).ready(function(){
					$('#debug_level').val('{{ debug_level }}');
				});
			</script>
		  </div>
		</div>
	</div>

	<!-- Remote Administration Agent(RAA) -->
	<h2>Remote Administration Agent</h2>
	<p class='OCdescription'>The SSL certificate(s) for web site within this user area may be administered remotely via the OneClickSSL Remote Administration Agent included within this plug-in. The Agent allows for the remote installation of SSL certificates for advanced lifecycle management such as monthly renewal programs and product upgrades. If enabled, the username for this account will be transferred to and held by GlobalSign in order to verify future requests for certificates. Please note that no passwords from this account will be transferred and any domains using RAA must first be enrolled manually by using the REDEEM Voucher process above with a suitable voucher that allows this function.</p>
	<div class="ocHighlight">
		<div class="ocSetting">
		  <div class="ocDsc">Allow Remote Administration Agent</div>
		  <div class="ocInput"><input type="checkbox" name="remote_admin"{{ remote_admin }}></div>
		</div>
	</div>
	
	<!-- Automatically assign available IP addresses -->
	<h2>Automatically assign available IP address</h2>
	<p class='OCdescription'>Automatically assign an IP address and update DNS information when an ip address is available for this specific user and the site is currently not availible on a dedicated ip address. While the old shared IP address is removed from the DNS the address wil not be removed from the website to make sure there is no downtime.</p>
	<div class="ocHighlight">
		<div class="ocSetting">
		  <div class="ocDsc">Automatically assign available IP address</div>
		  <div class="ocInput"><input type="checkbox" name="auto_ip"{{ auto_ip }}></div>
		</div>
	</div>
	
	<!-- Save button -->
	<input type="submit" value="{{ LANG::Save }}">
	
</form>
</div>
