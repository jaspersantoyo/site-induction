import { Injectable } from '@angular/core';

@Injectable()
export class LocalStorageService {

  /**
   * Set item to local storage
   * @param key Key string
   * @param value Object type value
   */
  public set<T>(key: string, value: T): void {
    let isUpdated: boolean = true;
    let item = this.get(key);
    // Compare Items
    if (item) {
      let result = JSON.stringify(value).localeCompare(JSON.stringify(item));
      if (result === 0) {
        isUpdated = false;
      }
    }
    // Save item to local storage
    if (isUpdated) {
      localStorage.setItem(key, JSON.stringify(value));
    }
  }

  /**
   * Get item to local storage
   * @param key Key string
   */
  public get<T>(key: string): T {
    let value: T;
    let localItem: string;

    // Get Item from local storage
    localItem = localStorage.getItem(key);
    if (localItem) {
      value = JSON.parse(localItem);
    }
    return value;
  }

  /**
   * Remove item from local storage
   * @param key Key string
   */
  public remove(key: string): void {
    localStorage.removeItem(key);
  }

  /**
   * Clear all record items in local storage
   */
  public clearAll(): void {
    localStorage.clear();
  }
}
