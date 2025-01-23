<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;

class PdfGeneratorService
{
    public function generateOrderPdf(array $orderDetails): Response
    {
        // Configuration de Dompdf
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $pdfOptions->set('isRemoteEnabled', true);

        // Instancier Dompdf
        $dompdf = new Dompdf($pdfOptions);

        // HTML pour le PDF
        $html = $this->renderHtmlForPdf($orderDetails);

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Définir le format du papier et l'orientation
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Générer la réponse PDF
        $output = $dompdf->output();
        $response = new Response($output);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'attachment; filename="order_' . $orderDetails['id'] . '.pdf"');

        return $response;
    }

    private function renderHtmlForPdf(array $orderDetails): string
{
    return '
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f9f9f9;
                color: #333;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: #fff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            }
            .header {
                text-align: center;
                margin-bottom: 30px;
            }
            .header img {
                width: 150px;
                margin-bottom: 10px;
            }
            .header h1 {
                color: #4A90E2;
                font-size: 28px;
                margin: 10px 0;
            }
            .header p {
                color: #777;
                font-size: 16px;
            }
            .order-details {
                margin-bottom: 30px;
            }
            .order-details h2 {
                color: #4A90E2;
                font-size: 24px;
                margin-bottom: 15px;
            }
            .order-details table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            .order-details th, .order-details td {
                border: 1px solid #ddd;
                padding: 12px;
                text-align: left;
            }
            .order-details th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
            .order-details tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .total-amount {
                text-align: right;
                font-size: 18px;
                font-weight: bold;
                margin-top: 20px;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 14px;
                color: #777;
                border-top: 1px solid #ddd;
                padding-top: 20px;
            }
            .footer p {
                margin: 5px 0;
            }
        </style>
        <div class="container">
            <div class="header">
                <img src="https://example.com/logo.png" alt="ZaiZen Logo">
                <h1>ZaiZen</h1>
                <p>Votre partenaire de confiance pour des handbags crochet uniques</p>
            </div>
            <div class="order-details">
                <h2>Détails de la Commande</h2>
                <table>
                    <tr>
                        <th>Nom du Client</th>
                        <td>' . $orderDetails['customerName'] . '</td>
                    </tr>
                    <tr>
                        <th>Adresse du Client</th>
                        <td>' . $orderDetails['customerAddress'] . '</td>
                    </tr>
                    <tr>
                        <th>Téléphone du Client</th>
                        <td>' . $orderDetails['customerPhone'] . '</td>
                    </tr>
                    <tr>
                        <th>Montant de la Commande</th>
                        <td>' . number_format($orderDetails['totalAmount'] - 7, 2) . ' DT</td>
                    </tr>
                    <tr>
                        <th>Frais de Livraison</th>
                        <td>7.00 DT</td>
                    </tr>
                </table>
                <div class="total-amount">
                    <strong>Montant Total: ' . number_format($orderDetails['totalAmount'] , 2) . ' DT</strong>
                </div>
            </div>
            <div class="footer">
                <p>Merci d\'avoir choisi ZaiZen pour vos handbags crochet !</p>
                <p>Contactez-nous : ZaiZen@gmail.com | Tél: +216 12 345 678</p>
                <p>Adresse: Rue de l\'Innovation, 1002 Tunis, Tunisie</p>
            </div>
        </div>
    ';
}
}