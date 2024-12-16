<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['admin'] != true) {
    header('HTTP/1.0 403 Forbidden');
    exit('Zugriff verweigert');
}

require 'vendor/autoload.php';
require_once 'db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$grouping = $_GET['grouping'] ?? 'user';
$class = $_GET['class'] ?? null;
$data = json_decode(file_get_contents('php://input'), true);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

switch ($grouping) {
    case 'user':
        generateUserReport($sheet, $data);
        break;
    case 'product':
        generateProductReport($sheet, $data);
        break;
    case 'class':
        generateClassReport($sheet, $data, $class);
        break;
}

$writer = new Xlsx($spreadsheet);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Bericht_' . $grouping . ($class ? '_' . $class : '') . '.xlsx"');
header('Cache-Control: max-age=0');

$writer->save('php://output');

function generateUserReport($sheet, $data) {
    $sheet->setCellValue('A1', 'Benutzer');
    $sheet->setCellValue('B1', 'Klasse');
    $sheet->setCellValue('C1', 'Gesamtausgaben');
    $sheet->setCellValue('D1', 'Produkte');

    $row = 2;
    foreach ($data as $userId => $userData) {
        $sheet->setCellValue('A' . $row, $userData['email']);
        $sheet->setCellValue('B' . $row, $userData['class_name']);
        $sheet->setCellValue('C' . $row, $userData['total_spent']);
        
        $products = [];
        foreach ($userData['products'] as $productKey => $product) {
            $products[] = $product['name'] . ' (Größe: ' . $product['size'] . ') - ' . $product['quantity'] . 'x';
        }
        $sheet->setCellValue('D' . $row, implode(', ', $products));
        
        $row++;
    }
}

function generateProductReport($sheet, $data) {
    $sheet->setCellValue('A1', 'Produkt');
    $sheet->setCellValue('B1', 'Größe');
    $sheet->setCellValue('C1', 'Gesamtverkäufe');
    $sheet->setCellValue('D1', 'Käufer');

    $row = 2;
    foreach ($data as $productKey => $productData) {
        $sheet->setCellValue('A' . $row, $productData['name']);
        $sheet->setCellValue('B' . $row, $productData['size']);
        $sheet->setCellValue('C' . $row, $productData['total_quantity']);
        
        $users = [];
        foreach ($productData['users'] as $userId => $user) {
            $users[] = $user['email'] . ' (' . $user['class_name'] . ') - ' . $user['quantity'] . 'x';
        }
        $sheet->setCellValue('D' . $row, implode(', ', $users));
        
        $row++;
    }
}

function generateClassReport($sheet, $data, $specificClass = null) {
    $sheet->setCellValue('A1', 'Klasse');
    $sheet->setCellValue('B1', 'Gesamtausgaben');
    $sheet->setCellValue('C1', 'Benutzer');
    $sheet->setCellValue('D1', 'Produkte');

    $row = 2;
    foreach ($data as $className => $classData) {
        $is_teacher = isset($classData['is_teacher']) && $classData['is_teacher'] == 1;
        $displayClassName = $is_teacher ? 'Lehrer' : $className;
        if ($specificClass && $displayClassName !== $specificClass) {
            continue;
        }
        $sheet->setCellValue('A' . $row, $displayClassName);
        $sheet->setCellValue('B' . $row, $classData['total_spent']);
        
        $users = [];
        foreach ($classData['users'] as $userId => $user) {
            $users[] = $user['email'] . ' - €' . number_format($user['total_spent'], 2);
        }
        $sheet->setCellValue('C' . $row, implode(', ', $users));
        
        $products = [];
        foreach ($classData['products'] as $productKey => $product) {
            $products[] = $product['name'] . ' (Größe: ' . $product['size'] . ') - ' . $product['quantity'] . 'x';
        }
        $sheet->setCellValue('D' . $row, implode(', ', $products));
        
        $row++;
    }
}
