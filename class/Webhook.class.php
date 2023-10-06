<?php
    class Webhook
    {
        private $product_key;//Código do produto
        private $trans_key;//Código da transação
        private $code_transaction;//Id do contrato
        private $product_name;//Nome do produto comprado
        private $client_name;//Nome do cliente
        private $client_email;//E-mail do cliente
        private $client_phone;//Celular do cliente
        private $date_payment;//Data do pagamento
        private $recurrence_interval_type;//Intervalo da recorrencia
        private $plan_name;//Nome do plano da recorrencia
        private $canceled_recurrence;//Caso a assinatura esteja cancelada indica a data de cancelamento
        private $expiration_trans;


        public function __construct(
         $product_key,//Código do produto
         $trans_key,//Código da transação
         $code_transaction,//Id do contrato
         $product_name,//Nome do produto comprado
         $client_name,//Nome do cliente
         $client_email,//E-mail do cliente
         $client_phone,//Celular do cliente
         $date_payment,//Data do pagamento
         $recurrence_interval_type,//Intervalo da recorrencia
         $plan_name,//Nome do plano da recorrencia
         $canceled_recurrence,//Caso a assinatura esteja cancelada indica a data de cancelamento
         $expiration_trans)
         {
            $this->product_key = $product_key;
            $this->trans_key = $trans_key;
            $this->code_transaction = $code_transaction;
            $this->product_name = $product_name;
            $this->client_name = $client_name;
            $this->client_email = $client_email;
            $this->client_phone = $client_phone;
            $this->date_payment = $date_payment;
            $this->recurrence_interval_type = $recurrence_interval_type;
            $this->plan_name = $plan_name;
            $this->canceled_recurrence = $canceled_recurrence;
            $this->expiration_trans = $expiration_trans;


            
         }

        //Verifica se uma assintura já existe
        public static function assinaturaExistente($code_transaction)
        {
            try
            {
                $con = Connection::connect();
            }
            catch(PDOException $p)
            {
                throw new PDOException($p->getMessage());
            }

            try
            {    
                $pst = $con->prepare("select id from tb_clientes where id_venda = :id_venda");
                $pst->bindParam(":id_venda", $code_transaction);
                $pst->execute();
                


                if($pst->rowCount() > 0)
                {           
                    return true;
                }
                else
                    return false;
            }
            catch(PDOException $p)
            {
                return false;
            }
        }

        //Atualiza a transação no banco de dados
        public function updateTransacao()
        {
            
            try
            {
                $con = Connection::connect();
            }
            catch(PDOException $p)
            {
                throw new PDOException($p->getMessage());
            }


            try
            {
                
                $pst = $con->prepare("update tb_clientes set  expiracao = :expiracao, status_compra = :status_compra, ultima_atualizacao = now() where id_venda = :id_venda");
                $pst->bindParam(":id_venda", $this->code_transaction);
                $pst->bindParam(":expiracao", $this->expiration_trans);
                $status = "active";
                if($this->expiration_trans < date("Y-m-d H:i:s"))
                    $status = "canceled";

                $pst->bindParam(":status_compra", $status);
                
                $pst->execute();
                
                if($pst->rowCount() > 0)
                {
                        
                    return array('success' => true);
                }
                else
                        return array('success' => false);
            }
            catch(PDOException $p)
            {
                throw new PDOException("Erro ao obter informações da licença ".$p->getMessage());
            }
        }



        //Insere um nova transação no banco de dados
        public function inserirTransacao()
        {
            
            try
            {
                $con = Connection::connect();
            }
            catch(PDOException $p)
            {
                throw new PDOException($p->getMessage());
            }

            try
            {
                
                $pst = $con->prepare("insert into tb_clientes (nome,email,senha,transacao,conta_real, conta_demo,status_compra,produto,expiracao,ultima_atualizacao,id_venda,metodo_pagamento,cpf,telefone) values (:nome,:email,:senha,:transacao,:conta_real,:conta_demo,:status_compra,:produto,:expiracao,now(),:id_venda,:metodo_pagamento,:cpf,:telefone)");
                $pst->bindParam(":nome", $this->client_name);
                $pst->bindParam(":email", $this->client_email);
                $pst->bindValue(":senha", md5("kiwi123"));
                $pst->bindParam(":transacao", $this->code_transaction);
                $pst->bindValue(":conta_real", 0);
                $pst->bindValue(":conta_demo", 0);
                $pst->bindValue(":status_compra", "active");
                $pst->bindParam(":produto", $this->product_name);
                $pst->bindParam(":expiracao", $this->expiration_trans);
                $pst->bindParam(":id_venda", $this->trans_key);
                $pst->bindValue(":metodo_pagamento", "N/A");
                $pst->bindValue(":cpf", "N/A");
                $pst->bindParam(":telefone", $this->client_phone);

                
                
                $pst->execute();
                
                echo $pst->debugDumpParams();

                if($pst->rowCount() > 0)
                {
                        
                    return array('success' => true);
                }
                else
                        return array('success' => false);
            }
            catch(PDOException $p)
            {
                throw new PDOException("Erro ao obter licenca ".$p->getMessage());
            }
        }
        
        
    }
?>