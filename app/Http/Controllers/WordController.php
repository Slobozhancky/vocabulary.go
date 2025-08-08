<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Word;

class WordController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|integer',
            'word' => 'required|string|max:255',
            'translation' => 'nullable|string|max:255',
        ]);

        $word = Word::create($data);

        return response()->json([
            'message' => 'Слово збережено',
            'word' => $word,
        ]);
    }
}
