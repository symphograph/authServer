<?php
require_once dirname($_SERVER['DOCUMENT_ROOT']) . '/vendor/autoload.php';

$origin = "https://{$_SERVER['SERVER_NAME']}";

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