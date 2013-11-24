<?php

/*

CometChat
Copyright (c) 2011 Inscripts

CometChat ('the Software') is a copyrighted work of authorship. Inscripts 
retains ownership of the Software and any copies of it, regardless of the 
form in which the copies may exist. This license is not a sale of the 
original Software or any copies.

By installing and using CometChat on your server, you agree to the following
terms and conditions. Such agreement is either on your own behalf or on behalf
of any corporate entity which employs you or which you represent
('Corporate Licensee'). In this Agreement, 'you' includes both the reader
and any Corporate Licensee and 'Inscripts' means Inscripts (I) Private Limited:

CometChat license grants you the right to run one instance (a single installation)
of the Software on one web server and one web site for each license purchased.
Each license may power one instance of the Software on one domain. For each 
installed instance of the Software, a separate license is required. 
The Software is licensed only to you. You may not rent, lease, sublicense, sell,
assign, pledge, transfer or otherwise dispose of the Software in any form, on
a temporary or permanent basis, without the prior written consent of Inscripts. 

The license is effective until terminated. You may terminate it
at any time by uninstalling the Software and destroying any copies in any form. 

The Software source code may be altered (at your risk) 

All Software copyright notices within the scripts must remain unchanged (and visible). 

The Software may not be used for anything that would represent or is associated
with an Intellectual Property violation, including, but not limited to, 
engaging in any activity that infringes or misappropriates the intellectual property
rights of others, including copyrights, trademarks, service marks, trade secrets, 
software piracy, and patents held by individuals, corporations, or other entities. 

If any of the terms of this Agreement are violated, Inscripts reserves the right 
to revoke the Software license at any time. 

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/

?>

(function(){
	jqcc(document).ready(function () {   
		jqcc.getScript('http://cdn.pubnub.com/pubnub-3.1.min.js', function() {
			cometready();
		});
	});
})();

function cometcall_function(id,td,callbackfn) {

	var socket = PUBNUB.init({
        'subscribe_key' : '<?php echo $subscribe_key;?>',
        'ssl'           : false
    });
    socket.ready();
	socket.subscribe({
        channel    : id,
        restore    : false,
        callback: function(data) {
				
			var incoming = data;
			incoming.message = unescape(incoming.message);

			if (callbackfn != '') {
				jqcc[callbackfn].newMessage(incoming);
			}

			var ts = Math.round(new Date().getTime() / 1000)+''+Math.floor(Math.random()*1000000)
			jqcc.cometchat.addMessage(incoming.from, incoming.message, incoming.self, 0, ts, 0, incoming.sent+td);
			
        }
    });
}

function chatroomcall_function(id) {

	var socket = PUBNUB.init({
        'subscribe_key' : '<?php echo $subscribe_key;?>',
        'ssl'           : false
    });
    socket.ready();
	socket.subscribe({
        channel    : id,
        restore    : false,
        callback: function(data) {
				
			var incoming = data;
			var ts = Math.round(new Date().getTime() / 1000) + '' + Math.floor(Math.random() * 1000000);
			addRawMessage(ts, incoming.message, incoming.from, incoming.sent);
			
        }
    });

}

function cometuncall_function(id) {
	PUBNUB.unsubscribe({ channel : id });
}