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

class Comet {
    private $ORIGIN        = 'pubsub.pubnub.com';
    private $LIMIT         = 1800;
    private $PUBLISH_KEY   = 'demo';
    private $SUBSCRIBE_KEY = 'demo';
    private $SECRET_KEY    = false;
    private $SSL           = false;
	

    /**
     * Pubnub
     *
     * Init the Pubnub Client API
     *
     * @param string $publish_key required key to send messages.
     * @param string $subscribe_key required key to receive messages.
     * @param string $secret_key optional key to sign messages.
     * @param string $origin optional setting for cloud origin.
     * @param boolean $ssl required for 2048 bit encrypted messages.
     */
	 
	function Comet($publish_key, $subscribe_key, $ssl = false, $origin  = 'pubsub.pubnub.com') 
	{
		Global $publish_key, $subscribe_key;
		
        $this->PUBLISH_KEY   = $publish_key;
        $this->SUBSCRIBE_KEY = $subscribe_key;
        $this->SECRET_KEY    = false;
        $this->SSL           = $ssl;

        if ($origin) $this->ORIGIN = $origin;

        if ($ssl) $this->ORIGIN = 'https://' . $this->ORIGIN;
        else      $this->ORIGIN = 'http://'  . $this->ORIGIN;
    }

    /**
     * Publish
     *
     * Send a message to a channel.
     *
     * @param array $args with channel and message.
     * @return array success information.
     */
    function publish($args) {
        ## Fail if bad input.
        if (!($args['channel'] && $args['message'])) {
            echo('Missing Channel or Message');
            return false;
        }

        ## Capture User Input
        $channel = $args['channel'];
        $message = json_encode($args['message']);

        ## Generate String to Sign
        $string_to_sign = implode( '/', array(
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $this->SECRET_KEY,
            $channel,
            $message
        ) );

        ## Sign Message
        $signature = $this->SECRET_KEY ? md5($string_to_sign) : '0';

        ## Fail if message too long.
        if (strlen($message) > $this->LIMIT) {
            echo('Message TOO LONG (' . $this->LIMIT . ' LIMIT)');
            return array( 0, 'Message Too Long.' );
        }

        ## Send Message
        return $this->_request(array(
            'publish',
            $this->PUBLISH_KEY,
            $this->SUBSCRIBE_KEY,
            $signature,
            $channel,
            '0',
            $message
        ));
    }

    /**
     * Subscribe
     *
     * This is BLOCKING.
     * Listen for a message on a channel.
     *
     * @param array $args with channel and message.
     * @return mixed false on fail, array on success.
     */
    function subscribe($args) {
        ## Capture User Input
        $channel   = $args['channel'];
        $callback  = $args['callback'];
        $timetoken = isset($args['timetoken']) ? $args['timetoken'] : '0';

        ## Fail if missing channel
        if (!$channel) {
            echo("Missing Channel.\n");
            return false;
        }

        ## Fail if missing callback
        if (!$callback) {
            echo("Missing Callback.\n");
            return false;
        }

        ## Begin Recusive Subscribe
        try {
            ## Wait for Message
            $response = $this->_request(array(
                'subscribe',
                $this->SUBSCRIBE_KEY,
                $channel,
                '0',
                $timetoken
            ));

            $messages          = $response[0];
            $args['timetoken'] = $response[1];

            ## If it was a timeout
            if (!count($messages)) {
                return $this->subscribe($args);
            }

            ## Run user Callback and Reconnect if user permits.
            foreach ($messages as $message) {
                if (!$callback($message)) return;
            }

            ## Keep Listening.
            return $this->subscribe($args);
        }
        catch (Exception $error) {
            sleep(1);
            return $this->subscribe($args);
        }
    }

    /**
     * History
     *
     * Load history from a channel.
     *
     * @param array $args with 'channel' and 'limit'.
     * @return mixed false on fail, array on success.
     */
    function history($args) {
        ## Capture User Input
        $limit   = +$args['limit'] ? +$args['limit'] : 10;
        $channel = $args['channel'];

        ## Fail if bad input.
        if (!$channel) {
            echo('Missing Channel');
            return false;
        }

        ## Get History
        return $this->_request(array(
            'history',
            $this->SUBSCRIBE_KEY,
            $channel,
            '0',
            $limit
        ));
    }

    /**
     * Time
     *
     * Timestamp from PubNub Cloud.
     *
     * @return int timestamp.
     */
    function time() {
        ## Get History
        $response = $this->_request(array(
            'time',
            '0'
        ));

        return $response[0];
    }

    /**
     * Request URL
     *
     * @param array $request of url directories.
     * @return array from JSON response.
     */
    private function _request($request) {
        $request = array_map( 'Comet::_encode', $request );
        array_unshift( $request, $this->ORIGIN );

        $ctx = stream_context_create(array(
            'http' => array( 'timeout' => 200 ) 
        ));

        return json_decode( @file_get_contents(
            implode( '/', $request ), 0, $ctx
        ), true );
    }

    /**
     * Encode
     *
     * @param string $part of url directories.
     * @return string encoded string.
     */
    private static function _encode($part) {
        return implode( '', array_map(
            'Comet::_encode_char', str_split($part)
        ) );
    }

    /**
     * Encode Char
     *
     * @param string $char val.
     * @return string encoded char.
     */
    private static function _encode_char($char) {
        if (strpos( ' ~`!@#$%^&*()+=[]\\{}|;\':",./<>?', $char ) === false)
            return $char;
        return rawurlencode($char);
    }
}

?>