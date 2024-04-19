import { create } from 'zustand';
import {
    type EventsType,
    type RoomsType,
    type StudentsType,
} from '~/definitions';

interface JsonStoreState {
    objects: {
        students: StudentsType | null;
        rooms: RoomsType | null;
        events: EventsType | null;
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
    clearStore: () =>
        set((state) => ({
            objects: { students: null, rooms: null, events: null },
        })),
}));

export default useJsonStore;
