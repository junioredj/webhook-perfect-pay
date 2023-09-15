<?php

    require_once "autoload.php";


      

    $json = json_decode(file_get_contents('php://input'));
    
    $dados = json_decode(file_get_contents('CONFIG.json'));


    if($json->token == $dados->token)//Verificação do token
    {
        $product_key = $json->product->code;//Código do produto
        $trans_key = $json->code;//Código da transação
        $code_transaction = $json->code;//Id do contrato
        $product_name = $json->product->name;//Nome do produto comprado
        $client_name = $json->customer->full_name;//Nome do cliente
        $client_email = $json->customer->email;//E-mail do cliente
        $client_phone = $json->customer->phone_number;//Celular do cliente
        $date_payment = $json->date_created;//Data do pagamento
        $plan_name = $json->plan->name;//Nome do plano da recorrencia
        $canceled_recurrence = null;//$json->recurrence_canceled_at;//Caso a assinatura esteja cancelada indica a data de cancelamento
        $expiration_trans = date("Y-m-d H:i:s");
        $recurrence_interval_type = null;

        if($json->sale_status_enum == 2)//Verifica se a fatura foi paga
        {


            if($json->subscription->status_event == "subscription_started" || $json->subscription->status_event == "subscription_renewed")//Assinatura
            {
                
                $expiration_trans = $json->subscription->next_charge_date;
                
            }
            else if($json->subscription->status_event == "approved")//Cobrança única
            {
                $expiration_trans = date('Y-m-d H:i:s', strtotime("+1000 years", strtotime($date_payment)));  
            }


            //Verifica se uma assintura ja existe para poder atualizar a antiga
            if(Webhook::assinaturaExistente($code_transaction))
            {
                
                $webhook = new Webhook($product_key, $trans_key, $code_transaction,$product_name,$client_name,$client_email,$client_phone,$date_payment,$recurrence_interval_type,$plan_name,$canceled_recurrence,$expiration_trans);
                echo json_encode($webhook->updateTransacao());
                
            }
            else
            {
                
                //Cadastra um novo registro
                $webhook = new Webhook($product_key, $trans_key, $code_transaction,$product_name,$client_name,$client_email,$client_phone,$date_payment,$recurrence_interval_type,$plan_name,$canceled_recurrence,$expiration_trans);
                echo json_encode($webhook->inserirTransacao());
            }


            
        }
        else if($json->sale_status_enum == 6 || $json->sale_status_enum == 7)//Verifica se a compra foi cancelada
        {
            $expiration_trans = date("Y-m-d H:i:s");
            $webhook = new Webhook($product_key, $trans_key, $code_transaction,$product_name,$client_name,$client_email,$client_phone,$date_payment,$recurrence_interval_type,$plan_name,$canceled_recurrence,$expiration_trans);
            echo json_encode($webhook->updateTransacao());
        }
    }
    else
        echo json_encode(array('api_key' => "invalid"));

    


