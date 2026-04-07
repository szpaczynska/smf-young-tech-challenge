<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Services\OcrAiParser;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        //ograniczenia co do wpisywanych wartości
        $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ];

        //ocr
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('products', 'public');
            $data['image'] = $path;

            $fullPath = storage_path('app/public/' . $path);
            $outputFile = tempnam(sys_get_temp_dir(), 'ocr');

            $tesseractExe = '"C:\Program Files\Tesseract-OCR\tesseract.exe"';
            $tessdataDir = "C:/Program Files/Tesseract-OCR/tessdata";
            $cmd = "$tesseractExe " . escapeshellarg($fullPath) . " " . escapeshellarg($outputFile) . " -l pol --tessdata-dir " . escapeshellarg($tessdataDir);

            exec($cmd, $output, $return_var);

            if ($return_var === 0 && file_exists($outputFile . ".txt")) {
                $textFromImage = trim(file_get_contents($outputFile . ".txt"));
                $data['ocr_text'] = $textFromImage;

            } else {
                \Log::error("OCR failed", ['cmd' => $cmd, 'return_var' => $return_var, 'output' => $output]);
                $data['ocr_text'] = null;
            }
        }

        Product::create($data);

        return redirect()->route('products.index');
    }

   
    public function show(string $id)
    {
        $product = Product::findOrFail($id);

        //agent ai
        if ($product->ocr_text && (empty($product->color) || empty($product->type))) {
                $parser = new OcrAiParser();
                $parsed = $parser->parse($product->ocr_text);

                if ($parsed) {
                    $product->color = $parsed['color'] ?? null;
                    $product->type = $parsed['type'] ?? null;
                } else {
                    
                    //w bazie zapisują się wartości wielkimi literami i z enterami, dlatego należało ujednolicić tekst
                    $normalizedText = mb_strtolower(preg_replace('/\s+/', ' ', $product->ocr_text));

                    if (preg_match('/(czerwona|czerwony|czarna|czarny|niebieski|niebieska|złoty|złota)/i', $normalizedText, $m)) {
                        $product->color = $m[0];
                    }

                    if (preg_match('/(sukienka|koszula|buty|bransoletka|koszulka)/i', $normalizedText, $m)) {
                        $product->type = $m[0];
                    }
                }
        }

        $product->save(); 
        return view('products.show', compact('product'));
    }
    
    public function edit(string $id)
    {
        $product = Product::findOrFail($id);
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'price' => 'required|numeric',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price']);
        
        //ocr
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('products', 'public');
            $data['image'] = $path;

            $fullPath = storage_path('app/public/' . $path);
            $outputFile = tempnam(sys_get_temp_dir(), 'ocr');

            $tesseractExe = '"C:\Program Files\Tesseract-OCR\tesseract.exe"';
            $tessdataDir = "C:/Program Files/Tesseract-OCR/tessdata";
            $cmd = "$tesseractExe " . escapeshellarg($fullPath) . " " . escapeshellarg($outputFile) . " -l pol --tessdata-dir " . escapeshellarg($tessdataDir);

            exec($cmd, $output, $return_var);

            if ($return_var === 0 && file_exists($outputFile . ".txt")) {
                $textFromImage = trim(file_get_contents($outputFile . ".txt"));
                $data['ocr_text'] = $textFromImage;
                
            } else {
                \Log::error("OCR failed", ['cmd' => $cmd, 'return_var' => $return_var, 'output' => $output]);
                $data['ocr_text'] = null;
            }
        }
                
            $product->update($data);
            return redirect()->route('products.index')->with('success', 'Produkt zaktualizowany!');
        }

   
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && file_exists(storage_path('app/public/' . $product->image))) {
            unlink(storage_path('app/public/' . $product->image));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produkt usunięty!');
    }

    
}
