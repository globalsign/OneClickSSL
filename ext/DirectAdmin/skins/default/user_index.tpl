<style type="text/css">
{% include "style.css" %}
</style>

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

<div class="ocContent">
<b>{{ LANG::FormIntroTitle }}</b><br>
<p>{{ LANG::FormIntroDescription }}</p>
<form action="" method="post" name="redeemVouchers">
  <table style="background-color: #F6F6F6; border-color: gray; border-width: 1px; border-spacing: 2px; width:600px">
    <tr>
      <td>
      
        <table>
          <tr>
            <td>
              <b>{{ LANG::FormDomainNameTitle }}:</b>
            </td>
            <td colspan=2>
              <input type="hidden" name="domain" value="{{ domain }}">{{ domain }}
            </td>
          </tr>
          <tr>
            <td colspan=3>
              * This domain name matches the servers hostname. This may result in the certificate failing to install. If you experience any unusual behaviour installing certificates on this domain you may have to change the servers hostname before attempting to install a certificate.
            </td>
          </tr>
          <tr>
            <td width=100>
              <b>{{ LANG::FormVoucherCodeTitle }}:</b>
            </td>
            <td>
              <input type="text" name="voucher" value="{{ voucher }}">
            </td>
            <td>
              {{ LANG::FormVoucherCodeHelp }}
            </td>
          </tr>
          <tr>
            <td>
              <b>{{ LANG::FormEmailAddressTitle }}:</b>
            </td>
            <td>
              <input type="text" name="email" value="{{ email }}">
            </td>
            <td>
              {{ LANG::FormEmailAddressHelp }}
            </td>
          </tr>
          <tr>
            <td colspan=3>
            
              <table>
                <tr>
                  <td>
                    <input type="checkbox" name="subagree">
                  </td>
                  <td>
                    {{ LANG::FormAgreement }} <a href="http://www.globalsign.com/repository/subscriber-agreement.html" target="_blank">{{ LANG::FormAgreementLink }}</a>
                  </td>
                  <td>
                    <input type="submit" value="{{ LANG::FormProcessButton }}">
                  </td>
                </tr>
              </table>
              
            </td>
            <td></td>
          </tr>
        </table>
        
      </td>
      <td valign=top>
        <a href="{{ voucher_url }}" target="_blank"><img src="/CMD_PLUGINS/OneClickSSL/images/voucher.png"></a>
      </td>
    </tr>
  </table>
</form>
</div>