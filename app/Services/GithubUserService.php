<?php
declare(strict_types=1);

namespace App\Services;

use App\Interfaces\StorageInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class GithubUserService
{
    public const STATUS_OK = 'OK';
    public const STATUS_INVALID = 'Invalid';
    public const CACHE_EXPIRY = 120;

    public function __construct(
        private StorageInterface $storage,
        public string|array $message = 'none',
        protected ?Request $request = null,
        private array $usersInfo = [],
        private array $usersInfoWithName = [],
        private array $usersInfoNoName = []
    )
    {
    }

    public function setRequest(Request $request): self
    {
        $this->request = $request;

        return $this;
    }

    public function averageFollowers(array $response): float
    {
        try {
            if (!empty($response['public_repos'])) {
                return (float)($response['followers'] / $response['public_repos']);
            }
        } catch (Throwable $e) {
            $errorMessage = sprintf('Class: GithubUserService; Method: averageFollowers; Message: %s', $e->getMessage());
            Log::error($errorMessage);
        }

        return (float)0;
    }

    private function categorizeUsers(array $response): void
    {
        if (!empty($response['name'])) {
            $this->usersInfoWithName[$response['name']] = [
                'name' => $response['name'],
                'login' => $response['login'],
                'company' => $response['company'],
                'followers' => $response['followers'],
                'public_repos' => $response['public_repos'],
                'average_followers' => $this->averageFollowers($response),
            ];

        } elseif (!empty($response['login'])) {
            // uses login as index to prepare for sorting
            // cannot sort with null indexes
            $this->usersInfoNoName[$response['login']] = [
                'name' => $response['name'],
                'login' => $response['login'],
                'company' => $response['company'],
                'followers' => $response['followers'],
                'public_repos' => $response['public_repos'],
                'average_followers' => $this->averageFollowers($response),
            ];
        }
    }

    /**
     * Sorts two sets of users
     * 1. Users that have name
     * 2. Users that dont have names are sorted using login
     */
    public function sortUsers(): self
    {
        asort($this->usersInfoWithName);
        asort($this->usersInfoNoName);

        return $this;
    }

    private function mergeUsersInfo(): self
    {
        $this->usersInfo = array_merge($this->usersInfo, $this->usersInfoWithName);
        $this->usersInfo = array_merge($this->usersInfo, $this->usersInfoNoName);

        return $this;
    }

    public function getUsersBasicInfo(): ?array
    {
        try {
            $raw = $this->request->getContent();
            $content = json_decode($raw, true);

            if (!empty($content['usernames'])) {
                // get full users info
                foreach ($content['usernames'] as $username) {
                    $response = $this->storage
                        ->setUrl("https://api.github.com/users")
                        ->setAction($username)
                        ->setDataSource("https://api.github.com/users/$username")
                        ->cache($username,self::CACHE_EXPIRY)
                        ->sendRequest()
                        ->toArray();

                    $this->categorizeUsers($response);
                }

                $this->sortUsers()->mergeUsersInfo();

                // handle response messages
                $this->message = 'success';
                $errorMessages = $this->storage->getErrorMessages();
                if (null !== $errorMessages) {
                    $this->message = $errorMessages;
                }

                return $this->usersInfo;
            }

        } catch (Throwable $e) {
            $errorMessage = sprintf('Class: GithubUserService; Method: getUsersBasicInfo; Message: %s', $e->getMessage());
            Log::error($errorMessage);
        }

        return null;
    }
}
