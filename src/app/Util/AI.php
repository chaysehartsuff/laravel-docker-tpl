<?php

namespace App\Util;

use OpenAI;
use OpenAI\Responses\Chat\CreateResponse;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Log;

class AI
{
    /** @var OpenAI\Client */
    private $client;

    /** @var array */
    private $messages = [];

    /** @var CreateResponse|null */
    private $lastResult;

    /** @var int */
    private $maxTokensPerRequest = 7000;

    /** @var int */
    private $tokenBuffer = 10;

    /**
     * Initialize the AI class with optional previous chat messages.
     *
     * @param array $previousChat An array of previous chat messages
     * @throws \OpenAI\Exceptions\InvalidArgumentException
     */
    public function __construct(array $previousChat = [])
    {
        $this->client = OpenAI::client(config('openai.api_key'));
        $this->messages = $previousChat;

        $config = new Gpt3TokenizerConfig();
        $this->tokenizer = new Gpt3Tokenizer($config);
    }

    /**
     * Send a message to the AI and store the response.
     *
     * @param string $message The message to send to the AI
     * @param string $role The role of the message sender (default: 'user')
     * @return self
     * @throws \OpenAI\Exceptions\ApiException
     * @throws \OpenAI\Exceptions\InvalidArgumentException
     */
    public function chat(string $message, string $role = 'user'): self
    {
        $chatArray = $this->splitMessage($message);
        $totalParts = count($chatArray);

        foreach ($chatArray as $index => $part) {
            $contextMessage = ($totalParts > 1) ? "(wait for message " . ($index + 1) . "/$totalParts) " : "";
            $this->messages[] = ['role' => $role, 'content' => $contextMessage . $part];
            echo "Sending messsage" . ($index + 1)/$totalParts ."\n";
            if ($index > 0) {
                sleep(60);
            }

            /** @var CreateResponse $result */
            $this->lastResult = $this->client->chat()->create([
                'model' => 'o3',
                'messages' => $this->messages,
            ]);

            $aiResponse = $this->lastResult->choices[0]->message->content;
            $this->messages[] = ['role' => 'assistant', 'content' => $aiResponse];
        }

        return $this;
    }

    /**
     * Split a message into multiple parts if it exceeds the token limit.
     *
     * @param string $message The message to split
     * @return array An array of message parts
     */
    private function splitMessage(string $message): array
    {
        $tokens = $this->countTokens($message);
        if ($tokens <= $this->maxTokensPerRequest) {
            return [$message];
        }

        $parts = [];
        $words = explode(' ', $message);
        $currentPart = '';
        $currentTokens = 0;

        foreach ($words as $word) {
            $wordTokens = $this->countTokens($word);
            if ($currentTokens + $wordTokens > $this->maxTokensPerRequest - $this->tokenBuffer) {
                $parts[] = trim($currentPart);
                $currentPart = $word . ' ';
                $currentTokens = $wordTokens;
            } else {
                $currentPart .= $word . ' ';
                $currentTokens += $wordTokens;
            }
        }

        if (!empty($currentPart)) {
            $parts[] = trim($currentPart);
        }

        return $parts;
    }


    private function countTokens(string $text): int
    {
        return $this->tokenizer->count($text);
    }

    /**
     * Get the content of the last AI response.
     *
     * @return string|null The content of the last AI response, or null if no response yet
     */
    public function response(): ?string
    {
        return $this->lastResult ? $this->lastResult->choices[0]->message->content : null;
    }

    /**
     * Clear the conversation history and last result.
     *
     * @return void
     */
    public function clear(): void
    {
        $this->messages = [];
        $this->lastResult = null;
    }

    /**
     * Get the current conversation history.
     *
     * @return array An array of message objects
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
}