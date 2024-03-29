# Malga Pagamentos for WooCommerce #

**Tags:** woocommerce, malga, gateway, payment  
**Requires at least:** 5.6  
**Tested up to:** 6.4 
**Stable tag:** 1.0.0  
**License:** GPLv3 or later  
**License URI:** http://www.gnu.org/licenses/gpl-3.0.html  
**Contributors:** MalgaTeam, Morais Junior

## Description ##

Receba pagamentos por cartão de crédito, boleto bancário e pix utilizando a [Malga](https://www.malga.io).

### Compatibilidade ###

Compatível com desde a versão 2.2.x do WooCommerce.

Este plugin funciona integrado com o [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) para os meios de pagamento brasileiro como PIX e Boleto, desta forma é possível enviar documentos do cliente como "CPF" ou "CNPJ".

### Colaborar ###

Você pode contribuir com código-fonte em nossa página no [GitHub](https://github.com/plughacker/malga-woocommerce).

## Installation ##

### Instalação do plugin: ###

* Envie os arquivos do plugin para a pasta wp-content/plugins, ou instale usando o instalador de plugins do WordPress.
* Ative o plugin.

### Requerimentos: ###

É necessário possuir uma conta na [Malga](https://www.malga.io) e ter instalado o [WooCommerce](http://wordpress.org/plugins/woocommerce/).

### Configurações do Plugin: ###

Com o plugin instalado acesse o admin do WordPress e entre em **"WooCommerce"** > **"Configurações"** > **"Pagamentos"** e configure as opção **"Malga"**:

- Habilite o meio de pagamento que você deseja, preencha as opções de **X-Client-Id**, **X-Api-Key** e **MerchantId** com os dados que você recebeu da Malga.
- Configure uma Chave secreta para o seu webhook e logo apois faça o registro do mesmo na api da Malga, se tiver duvidas pode consultar nossa [documentação](https://docs.plugpagamentos.com/#section/Criacao-de-um-webhook)

*Também será necessário utilizar o plugin [WooCommerce Extra Checkout Fields for Brazil](http://wordpress.org/plugins/woocommerce-extra-checkout-fields-for-brazil/) para poder enviar campos de CPF e CNPJ.*

Pronto, sua loja já pode receber pagamentos pela [Malga](https://www.malga.io/?lang=en).

Mais informações sobre nossa API pode consultar a [documentação](https://docs.malga.io/) ou entrar em contato :)

### Caso tenha que enviar um metadata ao PaymentFlow
No fluxo  de pagamentos temos a opção de enviar dados adicionais para o orquestrador, o mesmo pode ser feito utilizando o filter malga_payment_flow como no exemplo a baixo:

```
function example_metadata( $metadata, $order, $post  ) {
    foreach ( $order->get_items() as $item_id => $item ) { 
		if($item->get_name() == 'test'){
			$metadata['cnpj'] = '123';
		}
	}
    return $metadata;
}
add_filter( 'malga_payment_flow', 'example_metadata', 10, 3 );
```

### Caso tenha que enviar dados adicionais do comprador

Podemos vincular um comprador adicionando o seguinte filter

```
function example_payload( $payload  ) {
	$response = wp_remote_post( 'https://api.malga.io/v1/customers', array(
		'method'      => 'POST',
		'timeout'     => 45,
		'redirection' => 5,
		'httpversion' => '1.0',
		'blocking'    => true,
		'headers'     => array(
            'Content-Type' => 'application/json',
            'X-Client-Id' => '*****',
            'X-Api-Key' => '*****'
        ),
		'body' => '{
			"name": "Customer test",
			"email": "jose2@gmail.com",
			"phoneNumber": "21 98889999099",
			"document": {
				"type": "noDocument"
			}
		}',
		'data_format' => 'body',
		)
	);

	$customer = json_decode($response['body'], true);

	$payload["customerId"] = $customer['id'];

	return $payload;
} add_filter( 'malga_payload', 'example_payload', 10, 3 );
```

## Testes ##

Para rodar os testes unitários utilize o comando: php vendor/bin/phpunit dentro do containner do wordpress

## Lint ##

Para rodar o Lint utilize o comando: php vendor/bin/phplint dentro do containner do wordpress

## PHP_CodeSniffer ##

Para rodar o CodeSniffer utilize o comando: ./vendor/bin/phpcs wp-content/plugins/woocommerce-malga-payments dentro do containner do wordpress
