<?php
namespace CrazyRacoon\Apitradermax;

class ApiTradePayeer
{
    public function __construct( private array $arParams = [],private array $arError = [] )
    {
        if (!$this->arParams['id']) {
            throw new InvalidArgumentException("'id' cannot be empty.");
        }
        if (!$this->arParams['key']) {
            throw new InvalidArgumentException("'key' cannot be empty.");
        }
    }
    private function Request( array $req = [] ): array
    {
        $msec = round(microtime(true) * 1000);
        $req['post']['ts'] = $msec;

        $post = json_encode($req['post']);

        $sign = hash_hmac('sha256', $req['method'].$post, $this->arParams['key']);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://payeer.com/api/trade/".$req['method']);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "API-ID: ".$this->arParams['id'],
            "API-SIGN: ".$sign
        ]);

        $response = curl_exec($ch);
        unset($ch);

        $arResponse = json_decode($response, true);

        if ($arResponse['success'] !== true)
        {
            $this->arError = $arResponse['error'];
            throw new Exception($arResponse['error']['code']);
        }

        return $arResponse;
    }


    public function GetError(): array
    {
        return $this->arError;
    }


    public function Info(): array
    {
        $res = $this->Request(req: [
            'method' => 'info',
        ]);

        return $res;
    }


    public function Orders( string $pair = 'BTC_USDT' ): array
    {
        $res = $this->Request(req: [
            'method' => 'orders',
            'post' => [
                'pair' => $pair,
            ],
        ]);

        return $res['pairs'];
    }


    public function Account(): array
    {
        $res = $this->Request(req: [
            'method' => 'account',
        ]);

        return $res['balances'];
    }


    public function OrderCreate( array $req = [] ): array
    {
        $res = $this->Request( req: [
            'method' => 'order_create',
            'post' => $req,
        ] );

        return $res;
    }


    public function OrderStatus( array $req = [] ): array
    {
        $res = $this->Request(req: [
            'method' => 'order_status',
            'post' => $req,
        ]);

        return $res['order'];
    }

    public function OrdersCancel( array $req ): array
    {
        $res = $this->Request(req: [
            'method' => 'order_cancel',
            'post' => $req,
        ]);

        return $res;
    }
    public function OrderCancel( array $req ): bool
    {
        $res = $this->Request(req: [
            'method' => 'orders_cancel',
            'post' => $req,
        ]);

        return $res['success'];
    }


    public function MyOrders( array $req = [] ): array
    {
        $res = $this->Request(req: [
            'method' => 'my_orders',
            'post' => $req,
        ]);
        return $res['items'];
    }
    public function MyHistory( array $req = [] ): array
    {
        $res = $this->Request(req: [
            'method' => 'my_history',
            'post' => $req,
        ]);
        return $res['items'];
    }
    public function MyTrades( array $req = [] ): array
    {
        $res = $this->Request(req: [
            'method' => 'my_trades',
            'post' => $req,
        ]);
        return $res['items'];
    }
}
