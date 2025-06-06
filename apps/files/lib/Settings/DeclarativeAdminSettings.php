<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2024 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\Files\Settings;

use OCA\Files\Service\SettingsService;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Settings\DeclarativeSettingsTypes;
use OCP\Settings\IDeclarativeSettingsFormWithHandlers;

class DeclarativeAdminSettings implements IDeclarativeSettingsFormWithHandlers {

	public function __construct(
		private IL10N $l,
		private SettingsService $service,
		private IURLGenerator $urlGenerator,
	) {
	}

	public function getValue(string $fieldId, IUser $user): mixed {
		return match($fieldId) {
			'windows_support' => $this->service->hasFilesWindowsSupport(),
			default => throw new \InvalidArgumentException('Unexpected field id ' . $fieldId),
		};
	}

	public function setValue(string $fieldId, mixed $value, IUser $user): void {
		switch ($fieldId) {
			case 'windows_support':
				$this->service->setFilesWindowsSupport((bool)$value);
				break;
		}
	}

	public function getSchema(): array {
		return [
			'id' => 'files-filename-support',
			'priority' => 10,
			'section_type' => DeclarativeSettingsTypes::SECTION_TYPE_ADMIN,
			'section_id' => 'server',
			'storage_type' => DeclarativeSettingsTypes::STORAGE_TYPE_EXTERNAL,
			'title' => $this->l->t('Files compatibility'),
			'doc_url' => $this->urlGenerator->linkToDocs('admin-windows-compatible-filenames'),
			'description' => (
				$this->l->t('Allow to restrict filenames to ensure files can be synced with all clients. By default all filenames valid on POSIX (e.g. Linux or macOS) are allowed.')
				. "\n" . $this->l->t('After enabling the Windows compatible filenames, existing files cannot be modified anymore but can be renamed to valid new names by their owner.')
				. "\n" . $this->l->t('It is also possible to migrate files automatically after enabling this setting, please refer to the documentation about the occ command.')
			),

			'fields' => [
				[
					'id' => 'windows_support',
					'title' => $this->l->t('Enforce Windows compatibility'),
					'description' => $this->l->t('This will block filenames not valid on Windows systems, like using reserved names or special characters. But this will not enforce compatibility of case sensitivity.'),
					'type' => DeclarativeSettingsTypes::CHECKBOX,
					'default' => false,
				],
			],
		];
	}
}
