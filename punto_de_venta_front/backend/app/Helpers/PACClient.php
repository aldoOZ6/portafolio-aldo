<?php
namespace App\Helpers;
class PACClient {
    public static function timbrarMock($xmlPath, $orderData) {
        $fakeUuid = 'FAKE-UUID-' . uniqid();
        $timbradoPath = __DIR__ . '/../../../storage/invoices/timbrado_' . basename($xmlPath);
        $dir = dirname($timbradoPath);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        copy($xmlPath, $timbradoPath);
        $timbre = "\n<!-- TIMBRADO MOCK UUID: $fakeUuid | Fecha: " . date('c') . " -->\n";
        $timbre .= "<TimbreFiscalDigital UUID=\"$fakeUuid\" FechaTimbrado=\"" . date('c') . "\" />\n";
        file_put_contents($timbradoPath, $timbre, FILE_APPEND);
        return [
          'status' => 'timbrado',
          'uuid' => $fakeUuid,
          'xml' => $timbradoPath
        ];
    }
}
