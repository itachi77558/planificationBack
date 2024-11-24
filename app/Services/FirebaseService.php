<?php

namespace App\Services;

use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;
use Exception;

class FirebaseService
{
    private $firestore;

    public function __construct()
    {
        // Charger les credentials Firebase encodés en Base64 depuis l'environnement
        $base64Credentials = env('FIREBASE_CREDENTIALS');

        if (!$base64Credentials) {
            throw new Exception("Les credentials Firebase encodés en Base64 sont absents dans le fichier .env");
        }

        // Décoder les credentials Base64
        $decodedCredentials = base64_decode($base64Credentials);

        if (!$decodedCredentials) {
            throw new Exception("Impossible de décoder les credentials Firebase encodés en Base64");
        }

        // Créer un fichier temporaire pour les credentials décodés
        $tempFilePath = sys_get_temp_dir() . '/firebase_credentials.json';

        if (!file_put_contents($tempFilePath, $decodedCredentials)) {
            throw new Exception("Impossible d'écrire les credentials Firebase dans un fichier temporaire");
        }

        // Initialiser Firestore avec le fichier temporaire
        $this->firestore = new FirestoreClient([
            'keyFilePath' => $tempFilePath,
            'projectId' => env('FIREBASE_PROJECT_ID', 'transfertapp-65947')
        ]);
    }

    public function executeTransfer($senderId, $recipientPhone, $amount)
    {
        try {
            $senderRef = $this->firestore->collection('users')->document($senderId);
            $recipientQuery = $this->firestore->collection('users')
                ->where('phone', '=', $recipientPhone)
                ->documents();

            $recipientDocs = iterator_to_array($recipientQuery);
            if (empty($recipientDocs)) {
                throw new Exception('Destinataire non trouvé');
            }

            $recipientDoc = reset($recipientDocs);
            $recipientId = $recipientDoc->id();

            $transactionId = uniqid('trans_');
            $now = (new \DateTime())->format('Y-m-d H:i:s');

            $senderTransaction = [
                'description' => "Transfert planifié à $recipientPhone",
                'amount' => -$amount,
                'date' => $now,
                'type' => 'scheduled_transfer',
                'status' => 'completed',
                'transactionId' => $transactionId
            ];

            $recipientTransaction = [
                'description' => "Transfert planifié reçu",
                'amount' => $amount,
                'date' => $now,
                'type' => 'scheduled_transfer',
                'status' => 'completed',
                'transactionId' => $transactionId
            ];

            $this->firestore->runTransaction(function ($transaction) use (
                $senderRef,
                $recipientId,
                $amount,
                $senderTransaction,
                $recipientTransaction
            ) {
                $senderDoc = $transaction->snapshot($senderRef);
                $recipientRef = $this->firestore->collection('users')->document($recipientId);
                $recipientDoc = $transaction->snapshot($recipientRef);

                $senderBalance = $senderDoc->get('balance') ?? 0;
                $recipientBalance = $recipientDoc->get('balance') ?? 0;
                $senderTransactions = $senderDoc->get('transactions') ?? [];
                $recipientTransactions = $recipientDoc->get('transactions') ?? [];

                if ($senderBalance < $amount) {
                    throw new Exception('Solde insuffisant pour effectuer le transfert');
                }

                $transaction->update($senderRef, [
                    [
                        'path' => 'balance',
                        'value' => $senderBalance - $amount
                    ],
                    [
                        'path' => 'transactions',
                        'value' => array_merge($senderTransactions, [$senderTransaction])
                    ]
                ]);

                $transaction->update($recipientRef, [
                    [
                        'path' => 'balance',
                        'value' => $recipientBalance + $amount
                    ],
                    [
                        'path' => 'transactions',
                        'value' => array_merge($recipientTransactions, [$recipientTransaction])
                    ]
                ]);
            });

            return true;
        } catch (Exception $e) {
            Log::error('Erreur lors du transfert Firebase: ' . $e->getMessage());
            throw $e;
        }
    }
}
