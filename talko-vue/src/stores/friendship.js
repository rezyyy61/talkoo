// src/stores/friendship.js
import { defineStore } from 'pinia';
import axiosInstance from '@/axios';

export const useFriendshipStore = defineStore('friendship', {
  state: () => ({
    friends: [],
    userFriends: [],
    sentRequests: [],
    receivedRequests: [],
    friendshipStatuses: {},
    isLoading: false,
    error: null,
  }),
  actions: {
    async fetchFriendData() {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await axiosInstance.get('/friends');
        this.friends = response.data.friends || [];
        this.sentRequests = response.data.sent_requests || [];
        this.receivedRequests = response.data.received_requests || [];
      } catch (error) {
        console.error("Error fetching friendship data:", error);
        this.error = error.response?.data?.message || 'Failed to fetch friendship data.';
      } finally {
        this.isLoading = false;
      }
    },

    async fetchFriendAcceptedData() {
      this.isLoading = true;
      this.error = null;
      try {
        const response = await axiosInstance.get('/friends/accepted');
        this.friends = response.data.friends || []; // Updated to align with Laravel response
      } catch (error) {
        console.error("Error fetching accepted friends:", error);
        this.error = error.response?.data?.message || 'Failed to fetch accepted friends.';
      } finally {
        this.isLoading = false;
      }
    },

    async sendFriendRequest(friendId) {
      try {
        const response = await axiosInstance.post('/friends/request', { friend_id: friendId });

        // Add a minimal sent request record
        this.sentRequests.push({
          friend_id: friendId,
          name: 'Unknown',
          email: 'unknown@example.com',
          status: 'pending',
          requested_at: new Date().toISOString(),
        });

        this.friendshipStatuses[friendId] = 'pending';
        return response.data.message;
      } catch (error) {
        console.error("Error sending friend request:", error);
        throw error.response?.data?.message || 'Failed to send friend request.';
      }
    },

    async acceptFriendRequest(friendshipId) {
      try {
        await axiosInstance.post('/friends/accept', { friendship_id: friendshipId });
        const index = this.receivedRequests.findIndex(req => req.friendship_id === friendshipId);
        if (index !== -1) {
          const acceptedFriend = this.receivedRequests.splice(index, 1)[0];
          this.friends.push({
            id: acceptedFriend.id,
            name: acceptedFriend.name,
            email: acceptedFriend.email,
          });
          if (acceptedFriend.id) {
            this.friendshipStatuses[acceptedFriend.id] = 'accepted';
          }
        }
        return 'Friend request accepted.';
      } catch (error) {
        console.error("Error accepting friend request:", error);
        throw error.response?.data?.message || 'Failed to accept friend request.';
      }
    },

    async declineFriendRequest(friendshipId) {
      try {
        await axiosInstance.post('/friends/decline', { friendship_id: friendshipId });
        const declinedRequest = this.receivedRequests.find(req => req.friendship_id === friendshipId);
        if (declinedRequest && declinedRequest.id) {
          this.friendshipStatuses[declinedRequest.id] = 'none';
        }
        this.receivedRequests = this.receivedRequests.filter(req => req.friendship_id !== friendshipId);
        return 'Friend request declined.';
      } catch (error) {
        console.error("Error declining friend request:", error);
        throw error.response?.data?.message || 'Failed to decline friend request.';
      }
    },

    async getFriendshipStatuses(userIds) {
      if (!Array.isArray(userIds) || userIds.length === 0) {
        return {};
      }
      try {
        const response = await axiosInstance.post('/friends/statuses', { friend_ids: userIds });
        const statuses = response.data;
        for (const [id, status] of Object.entries(statuses)) {
          this.friendshipStatuses[id] = status;
        }
        return statuses;
      } catch (error) {
        console.error("Error fetching friendship statuses:", error);
        throw error.response?.data?.message || 'Failed to fetch friendship statuses.';
      }
    },
  },
  getters: {
    receivedRequestsCount: (state) => state.receivedRequests.length,
    sentRequestsCount: (state) => state.sentRequests.length,
    friendsCount: (state) => state.friends.length,
  },
});
