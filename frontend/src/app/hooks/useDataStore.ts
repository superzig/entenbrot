import {create} from 'zustand';

interface JsonStoreState {
    objects: {
        students: object | null;
        rooms: object | null;
        events: object | null;
    };
    addJson: (pageKey: 'students' | 'rooms' | 'events', json: object) => void;
    clearStore: () => void;
}
const useJsonStore = create<JsonStoreState>((set) => ({
    objects: { students: null, rooms: null, events: null },
    addJson: (pageKey, json) =>
        set((state) => ({
            objects: { ...state.objects, [pageKey]: json },
        })),
    clearStore: () => set((state) => ({ objects: { students: null, rooms: null, events: null } })),
}));

export default useJsonStore;