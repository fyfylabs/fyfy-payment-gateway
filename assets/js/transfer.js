

// Get the modal
var modal = document.getElementById("fyfymodal");


// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
};



jQuery(function ($) {

    /**
     * Pantom transfer
     * @returns {Promise<void>}
     */
    const getPhanProvider = () => {

        return new Promise((resolve) => {
            if ("solana" in window) {
                const provider = window.solana;
                console.log('provier---', provider);
                if (provider.isPhantom) {
                    console.log("Is Phantom installed?  ", provider.isPhantom);
                    resolve(provider);
                }
            } else {
                window.open("https://www.phantom.app/", "_blank");
            }
        });

    };

    /**
     * SolFare transfer
     * @returns {Promise<void>}
     */
    const getSolProvider = () => {
        return new Promise((resolve) => {
            if ("solflare" in window) {
                const provider = window.solflare;
                console.log('provier---', provider);
                if (provider.isSolflare) {
                    console.log("Is solflare installed?  ", provider.isSolflare);
                    resolve(provider);
                }
            } else {
                window.open("https://solflare.com", "_blank");
            }
        });
    };


    /**
     * Call the ajax to check the order status by Mobile every 2 seconds.
     */

    var check_order_status = {

        start: function() {
            this.interval = setInterval(check_status_by_ajax, 2000);
        },

        stop:function(){
            clearInterval(this.interval);
        }

    };

    const check_status_by_ajax = () => {
        $.ajax( {
            url: wpFyFYApi.ajax_url,
            type: 'post',
            data: {
                action: 'check_order_status_by_m',
                nonce: wpFyFYApi.ajax_nonce,   // pass the nonce here
                order_id: order_id,
            },
            success( result ) {

                if ( result.data.message === 'verified' ) {

                    check_order_status.stop();

                    $('#fyfymodal').css('display', 'block');
                    $('#fyfyBtn').removeClass('loading');

                }
            },
        });
    };

    check_order_status.start();

    /**
     *
     * @param provider
     * @returns {Promise<void>}
     */
    async function transferSOL(provider) {

        if(!$('#fyfyBtn').hasClass('loading')) $('#fyfyBtn').addClass('loading');

        const publicKey = await provider.connect().then(res => res.toString());

        if(publicKey){
            try {

                if (!storeWalletAddress) throw new Error('Error: Empty Store WalletAddress!');
                if (!contractaddress) throw new Error('Error: Empty Token Contract Address!');
                if (!amount) throw new Error('Error: Empty Token Amount!');

                const connection = new solanaWeb3.Connection(solanaWeb3.clusterApiUrl("testnet"), 'confirmed');

                var recieverWallet = new solanaWeb3.PublicKey(storeWalletAddress);
                // var test = new solanaWeb3.Keypair.generate();


                // console.log(test);


                var fromAirdropSignature = await connection.requestAirdrop(
                    provider.publicKey,
                    solanaWeb3.LAMPORTS_PER_SOL,
                );

                // Wait for airdrop confirmation
                await connection.confirmTransaction(fromAirdropSignature);


                // Construct USDC token class
                var USDC_pubkey = new solanaWeb3.PublicKey(contractaddress);
                var USDC_Token = new splToken.Token(
                    connection,
                    USDC_pubkey,
                    splToken.TOKEN_PROGRAM_ID,
                    provider
                );

                // Create associated token accounts for my token if they don't exist yet
                var fromTokenAccount = await USDC_Token.getOrCreateAssociatedAccountInfo(
                    provider.publicKey
                );

                var toTokenAccount = await USDC_Token.getOrCreateAssociatedAccountInfo(
                    recieverWallet
                );
                // Add token transfer instructions to transaction
                var transaction = new solanaWeb3.Transaction()
                    .add(
                        splToken.Token.createTransferInstruction(
                            splToken.TOKEN_PROGRAM_ID,
                            fromTokenAccount.address,
                            toTokenAccount.address,
                            provider.publicKey,
                            [],
                            amount * 1000000
                        )
                    );

                // Setting the variables for the transaction
                transaction.feePayer = await provider.publicKey;
                let blockhashObj = await connection.getRecentBlockhash();
                transaction.recentBlockhash = await blockhashObj.blockhash;


                // Transaction constructor initialized successfully
                if (transaction) {
                    console.log("Txn created successfully");
                }

                // Request creator to sign the transaction (allow the transaction)
                let signed = await provider.signTransaction(transaction);
                // The signature is generated
                let signature = await connection.sendRawTransaction(signed.serialize());
                // Confirm whether the transaction went through or not
                await connection.confirmTransaction(signature);
                console.log(signature);


                $.ajax({
                    url: wpFyFYApi.root + 'fyfy-api/v1/check_payment',
                    method: 'POST',
                    beforeSend: function (xhr){
                        xhr.setRequestHeader("X-WP-Nonce", wpFyFYApi.nonce);
                    },
                    data: {
                        signature: signature,
                        order_id: order_id,
                        type: 'web'
                    },
                    success: function( data ) {
                        $('#fyfymodal').css('display', 'block');
                        $('#fyfyBtn').removeClass('loading');
                        check_order_status.stop();
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log(errorThrown);
                        $('#fyfyBtn').removeClass('loading');
                        check_order_status.stop();
                    }
                });

            } catch (error) {
                console.log(error);
                $('#fyfyBtn').removeClass('loading');
            }
        }
    }

    $(document.body).on('click', '.pay_broswer_button.phan', async function () {

        // Detecing and storing the phantom wallet of the user (creator in this case)
        var provider = await getPhanProvider();

        transferSOL(provider);
    });
    /**
     * SolFare transfer
     */
    $(document.body).on('click', '.pay_broswer_button.sol', async function () {


        // Detecing and storing the Solfare wallet of the user (creator in this case)
        var provider = await getSolProvider();

        transferSOL(provider);
    });


    new QRCode(document.getElementById("qrcode"), qr_data);




});





