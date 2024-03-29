<?php
declare(strict_types=1);

namespace App\Application\Actions\User;

use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\DomainException\DomainRecordValidator;
use App\Domain\User\User;
use App\Domain\User\UserValidator;
use Psr\Http\Message\ResponseInterface as Response;

class UpdateUserAction extends UserAction {

    /**
     * {@inheritDoc}
     */
    public function action() : Response
    {
        /** CHECK IF USER EXISTS */
        $username = (string) $this->resolveArg('username');

        $user = $this->userRepository->findByUsername($username);

        if (!isset($user)) {
            $this->logger->error("User of username `${username}` doesn't exists.");

            throw new DomainRecordNotFoundException("User of username `${username}` doesn't exists.");
        }

        /** VALIDATE PARAMS */
        $input = json_decode($this->request->getBody()->__toString(), true);

        if (isset($input['name'])) {
            $user->setName($input['name']);
        }

        if (isset($input['email']))
            $user->setEmail($input['email']);

        if (isset($input['password']))
            $user->setPassword($input['password']);

        $userValidator = new UserValidator($user);

        $check = $userValidator->validate();

        if (!$check) {
            $messages = $userValidator->getMessagesErrors();

            $this->logger->error("UserValidator launched FALSE");

            throw new DomainRecordValidator($messages);
        }

        /** UPDATE USER */
        $user = $this->userRepository->save($user);

        $this->logger->info("User of username `{$user->getUsername()}` updated with success.");

        return $this->respondWithData($user);
    }

}

?>