--------------------
Snippet: Eway
--------------------
Version: 0.1
First Released: October 2012
Author: Everett Griffiths <everett@carftsmancoding.com>
License: GNU GPLv2 (or later at your option)

This component implements a payment solution for the Eway Online Payment Gateway (http://www.eway.com.au/).
Eway offers payment solutions for Australia, New Zealand, and the United Kingdom.

The current Snippet offers an integration of the Shared Payment API.  The Shared Payment API
is sorta like PayPal for our friends in Australia, New Zealand, and the UK.

These Snippets require the cUrl PHP extension.  Check your phpinfo()!


== Sample Usage ==

First, you should set the eway.customerID System Setting so it uses your Eway customer ID number.
For testing, you can use the number "87654321", but please refer to Eway's documentation: using
the test account only works if you use certain customer names and certain transaction amounts.

On a page, put the EwaySharedPayments snippet along with a form.  You can use the sample form 
provided for you in the "eway_sample_form" Chunk:

[[!EwaySharedPayments]]

[[$eway_sample_form]]


Or, you can provide your own form, using the sample as a guide.  

If you would like to redirect to a different page after a transaction has been completed, you can change the 
&redirectUrl parameter to reference the page ID of another page, e.g.

[[!EwaySharedPayments? &redirectID=`123`]]

You can put a thank you message on the destination page.  Note that Eway does post data back to the page referenced 
by the &redirectUrl parameter (the default page is the page where the EwaySharedPayments Snippet is called).  Eway 
posts back a single value: AccessPaymentCode.  The EwaySharedPayments Snippet will listen for that parameter and 
if encountered, the &successTpl Chunk will be used to format the output, so you can use the EwaySharedPayments Snippet
on the "Thank You" page, or for more flexibility, write your own Snippet that listens for the $_POST['AccessPaymentCode']
parameter and put it on the page you referenced in the &redirectID


See some of Eway's docs:

http://www.eway.com.au/developers/api/shared-payments
https://eway.secure.force.com/PartnerPortal/Resources-AU


Thanks for using Eway!

Everett Griffiths
everett@carftsmancoding.com