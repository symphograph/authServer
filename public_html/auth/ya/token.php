<?php

use Symphograph\Bicycle\Env\Server\ServerEnv;

require_once dirname(__DIR__, 3) . '/vendor/autoload.php';

$origin = "https://" . ServerEnv::SERVER_NAME();

?>
<head>
    <script src="https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-token-with-polyfills-latest.js"></script>
</head>
<script>
    window.onload = () => {
        YaSendSuggestToken(
            '<?php echo $origin?>',
            {
                flag: true,
                anyData: {
                    ttt: '123'
                }
            }
        )
    }
</script>