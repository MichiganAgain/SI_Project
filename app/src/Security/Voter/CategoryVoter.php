<?php
/**
 * Category voter.
 */

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CategoryVoter.
 */
class CategoryVoter extends Voter
{
    /**
     * Edit permission.
     *
     * @const string
     */
    public const EDIT = 'EDIT';

    /**
     * View permission.
     *
     * @const string
     */
    public const VIEW = 'VIEW';

    /**
     * Delete permission.
     *
     * @const string
     */
    public const DELETE = 'DELETE';

    /**
     * Check permission.
     *
     * @const string
     */
    public const MANAGE = 'MANAGE';

    /**
     * Security helper.
     *
     * @var Security
     */
    private Security $security;

    /**
     * OrderVoter constructor.
     *
     * @param Security $security Security helper
     */
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * Determines if the attribute and subject are supported by this voter.
     *
     * @param string $attribute An attribute
     * @param mixed  $subject   The subject to secure, e.g. an object the user wants to access or any other PHP type
     *
     * @return bool Result
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE, self::MANAGE])
            && ($subject === null || $subject instanceof Category);
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     * It is safe to assume that $attribute and $subject already passed the "supports()" method check.
     *
     * @param string         $attribute Permission name
     * @param mixed          $subject   Object
     * @param TokenInterface $token     Security token
     *
     * @return bool Vote result
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::MANAGE:
                return $this->canManage($user);
            case self::EDIT:
                return $this->canEdit($subject, $user);
            case self::VIEW:
                return $this->canView($subject, $user);
            case self::DELETE:
                return $this->canDelete($subject, $user);
        }

        return false;
    }

    /**
     * Checks if user can edit category.
     *
     * @param Category $category Category entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canEdit(Category $category, User $user): bool
    {
        if($this->security->isGranted('ROLE_ADMIN')){
            return true;
        }
        else{
            return $category->getAuthor() === $user;
        }

    }

    /**
     * Checks if user can view category.
     *
     * @param Category $category Category entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canView(Category $category, User $user): bool
    {
        if($this->security->isGranted('ROLE_ADMIN')){
            return true;
        }
        else{
            return $category->getAuthor() === $user;
        }
    }

    /**
     * Checks if user can delete category.
     *
     * @param Category $category Category entity
     * @param User $user User
     *
     * @return bool Result
     */
    private function canDelete(Category $category, User $user): bool
    {
        if($this->security->isGranted('ROLE_ADMIN')){
            return true;
        }
        else{
            return $category->getAuthor() === $user;
        }
    }

    /**
     * Checks if user can delete category.
     *
     * @param User $user User
     *
     * @return bool Result
     */
    private function canManage(User $user): bool
    {
        return $this->security->isGranted('ROLE_ADMIN');
    }
}
