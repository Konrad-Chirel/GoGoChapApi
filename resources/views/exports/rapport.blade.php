<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapport {{ $type }}</title>
    <link href="{{ public_path('css/pdf.css') }}" rel="stylesheet">
</head>
<body>
    <div class="header">
        <h1>Rapport {{ ucfirst($type) }}</h1>
        <div class="date-info">
            <p>Période : du {{ $dateDebut }} au {{ $dateFin }}</p>
        </div>
    </div>

    @if($type === 'financier')
        <div class="stats">
            <h3>Statistiques Financières</h3>
            <div class="stat-item">
                <span>Total des ventes</span>
                <span>{{ number_format($data->sum('total_price'), 2, ',', ' ') }} €</span>
            </div>
            <div class="stat-item">
                <span>Frais de livraison</span>
                <span>{{ number_format($data->sum('delivery_fee'), 2, ',', ' ') }} €</span>
            </div>
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>ID Commande</th>
                <th>Date</th>
                <th>Prix Total</th>
                <th>Frais Livraison</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $order)
                <tr>
                    <td>{{ $order->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}</td>
                    <td>{{ number_format($order->total_price, 2, ',', ' ') }} €</td>
                    <td>{{ number_format($order->delivery_fee, 2, ',', ' ') }} €</td>
                    <td>{{ ucfirst($order->order_status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Généré le {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
