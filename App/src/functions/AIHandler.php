<?php

namespace functions;

class AIHandler
{
    private string $destinationUrl = 'http://ai_game_container:5000/';

    public function aiCall($moveNumber, $hands, $board) {
        $moveInfo = [
            'move_number' => $moveNumber,
            'hand' => $hands,
            'board' => $board,
        ];

        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($moveInfo),
            )
        );
        $context = stream_context_create($opts);
        return file_get_contents($this->destinationUrl, false, $context);
    }
 }
