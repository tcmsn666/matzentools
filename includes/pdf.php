<?php
declare(strict_types=1);

function pdfAvailable(): bool
{
    return is_file(__DIR__ . '/../vendor/fpdf/fpdf.php');
}

function fpdiAvailable(): bool
{
    return is_file(__DIR__ . '/../vendor/fpdi/src/Fpdi.php') || is_file(__DIR__ . '/../vendor/fpdi/Fpdi.php');
}

function createBasicPdf(string $title, string $content): void
{
    if (!pdfAvailable()) {
        throw new RuntimeException('FPDF ist nicht installiert. Bitte /vendor/fpdf/fpdf.php bereitstellen.');
    }
    require_once __DIR__ . '/../vendor/fpdf/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, mb_convert_encoding($title, 'ISO-8859-1', 'UTF-8'), 0, 1);
    $pdf->SetFont('Arial', '', 11);
    foreach (explode("\n", $content) as $line) {
        $pdf->MultiCell(0, 7, mb_convert_encoding($line, 'ISO-8859-1', 'UTF-8'));
    }
    $pdf->Output('I', 'matzentools.pdf');
}

function prepareFpdiImportPlaceholder(string $sourceFile): string
{
    if (!fpdiAvailable()) {
        return 'FPDI ist noch nicht installiert. Bitte /vendor/fpdi/ bereitstellen.';
    }
    return 'FPDI kann später zum Ergänzen bestehender PDF-Dokumente verwendet werden: ' . basename($sourceFile);
}
