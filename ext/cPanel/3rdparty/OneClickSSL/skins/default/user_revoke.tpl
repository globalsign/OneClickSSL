<style type="text/css">
{% include "style.css" %}
</style>

<!-- Revocation -->
<div class="ocContent">
<form action="" method="post">
	<h1>{{ LANG::FormProcessRevokeButton }}</h1>
	<p class='ocDescription'>{{ LANG::FormRevokeWarning }}</p>
	<div class="ocHighlight">
		<div class="ocSetting">
		  <div class="ocDsc">{{ LANG::FormDomainNameTitle }}</div>
		  <div class="ocInput">
             <select name="domain">
				{{ domainOptions }}
              </select>
		  </div>
		</div>
		<div class="ocSetting">
		  <div class="ocDsc">{{ LANG::FormEmailAddressTitle }}</div>
		  <div class="ocInput"><input type="text" name="email" value="{{ email }}" size="40"></div>
		</div>
		<div class="ocSetting">
		  <div class="ocDsc">{{ LANG::FormSerialNumberTitle }} <span style="font-size:6pt;">({{ LANG::FormSerialNumberHelp }})</span></div>
		  <div class="ocInput"><input type="text" name="voucher" value="{{ voucher }}" size="40"></div>
		</div>
	</div>
	
	<input type="hidden" value="1" name="revoke">
	<input type="submit" value="{{ LANG::FormProcessRevokeButton }}">
	<img src="images/exclamation.png" alt="">
</form>
</div>