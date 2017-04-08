<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<title>Title</title>
	</head>
	<body>
        <div>
			<div id="paypal-button-container"></div>
        </div>

		<script src="https://www.paypalobjects.com/api/checkout.js"></script>
		<script>

		    // Render the PayPal button

			paypal.Button.render({

	        // Set your environment

	        env: 'sandbox', // sandbox | production

	        // Wait for the PayPal button to be clicked

	        payment: function(resolve, reject) {

	            // Make a call to the merchant server to set up the payment

				paypal.request.post('http://localhost/tshirtsite/public/createPayment', {
			     // js object used to create payment
			     }).then(function(data) {
			         resolve(data.id);
			     }).catch(function(err) {
			         reject(err);
			     });
	        },

	        // Wait for the payment to be authorized by the customer

	        onAuthorize: function(data, actions) {

	            // Make a call to the merchant server to execute the payment
	            return paypal.request.post('http://localhost/tshirtsite/public/executePayment', {
	                payToken: data.paymentID,
	                payerId: data.payerID
	            }).then(function (res) {

	                document.querySelector('#paypal-button-container').innerText = 'Payment Complete!';
	            });
	        }

	    }, '#paypal-button-container');

		</script>
	</body>
</html>
